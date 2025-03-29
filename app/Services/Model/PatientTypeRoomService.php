<?php

namespace App\Services\Model;

use App\DTOs\PatientTypeRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PatientTypeRoom\InsertPatientTypeRoomIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientTypeRoomRepository;
use App\Repositories\PatientTypeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PatientTypeRoomService
{
    protected $patientTypeRoomRepository;
    protected $patientTypeRepository;
    protected $roomRepository;
    protected $params;
    public function __construct(PatientTypeRoomRepository $patientTypeRoomRepository, PatientTypeRepository $patientTypeRepository, RoomRepository $roomRepository)
    {
        $this->patientTypeRoomRepository = $patientTypeRoomRepository;
        $this->patientTypeRepository = $patientTypeRepository;
        $this->roomRepository = $roomRepository;
    }
    public function withParams(PatientTypeRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->patientTypeRoomRepository->applyJoins();
            $data = $this->patientTypeRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->patientTypeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->patientTypeRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
            $data = $this->patientTypeRoomRepository->applyPatientTypeIdFilter($data, $this->params->patientTypeId);
            $count = $data->count();
            $data = $this->patientTypeRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->patientTypeRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->patientTypeRoomName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->patientTypeRoomName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->patientTypeRoomRepository->applyJoins();
                $data = $this->patientTypeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->patientTypeRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
                $data = $this->patientTypeRoomRepository->applyPatientTypeIdFilter($data, $this->params->patientTypeId);
                $count = $data->count();
                $data = $this->patientTypeRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->patientTypeRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->patientTypeRoomName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->patientTypeRoomName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->patientTypeRoomRepository->applyJoins()
                    ->where('his_patient_type_room.id', $id);
                $data = $this->patientTypeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_room'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'app_creator' => $this->params->appCreator,
            'app_modifier' => $this->params->appModifier,
        ];
    }
    public function createPatientTypeRoom($request)
    {
        try {
            if ($request->room_id != null) {
                $id = $request->room_id;
                $data = $this->roomRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->patient_type_ids !== null) {
                        $patient_type_ids_arr = explode(',', $request->patient_type_ids);
                        foreach ($patient_type_ids_arr as $key => $item) {
                            $patient_type_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->patient_types()->sync($patient_type_ids_arr_data);
                    } else {
                        $deleteIds = $this->patientTypeRoomRepository->deleteByRoomId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->patientTypeRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->patientTypeRoomRepository->getByRoomIdAndPatientTypeIds($id, $patient_type_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertPatientTypeRoomIndex($item, $this->params->patientTypeRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->patient_type_id != null) {
                $id = $request->patient_type_id;
                $data = $this->patientTypeRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->room_ids !== null) {
                        $room_ids_arr = explode(',', $request->room_ids);
                        foreach ($room_ids_arr as $key => $item) {
                            $room_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->rooms()->sync($room_ids_arr_data);
                    } else {
                        $deleteIds = $this->patientTypeRoomRepository->deleteByPatientTypeId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->patientTypeRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->patientTypeRoomRepository->getByPatientTypeIdAndRoomIds($id, $room_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertPatientTypeRoomIndex($item, $this->params->patientTypeRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->patientTypeRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_room'], $e);
        }
    }
}
