<?php

namespace App\Services\Model;

use App\DTOs\ServiceRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceRoom\InsertServiceRoomIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceRoomRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Facades\DB;

class ServiceRoomService
{
    protected $serviceRoomRepository;
    protected $serviceRepository;
    protected $roomRepository;
    protected $params;
    public function __construct(ServiceRoomRepository $serviceRoomRepository, ServiceRepository $serviceRepository, RoomRepository $roomRepository)
    {
        $this->serviceRoomRepository = $serviceRoomRepository;
        $this->serviceRepository = $serviceRepository;
        $this->roomRepository = $roomRepository;
    }
    public function withParams(ServiceRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceRoomRepository->applyJoins();
            $data = $this->serviceRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
            $data = $this->serviceRoomRepository->applyServiceIdFilter($data, $this->params->serviceId);
            $count = $data->count();
            $data = $this->serviceRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->serviceRoomName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_service_id_' . $this->params->serviceId . '_room_id_' . $this->params->roomId . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->serviceRoomRepository->applyJoins();
                $data = $this->serviceRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->serviceRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
                $data = $this->serviceRoomRepository->applyServiceIdFilter($data, $this->params->serviceId);
                $count = $data->count();
                $data = $this->serviceRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->serviceRoomName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->serviceRoomRepository->applyJoins()
                    ->where('his_service_room.id', $id);
                $data = $this->serviceRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_room'], $e);
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
            'is_priority' => $request->is_priority,
        ];
    }
    public function createServiceRoom($request)
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
                    if ($request->service_ids !== null) {
                        $service_ids_arr = explode(',', $request->service_ids);
                        foreach ($service_ids_arr as $key => $item) {
                            $service_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->services()->sync($service_ids_arr_data);
                    } else {
                        $deleteIds = $this->serviceRoomRepository->deleteByRoomId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->serviceRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->serviceRoomRepository->getByRoomIdAndServiceIds($id, $service_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertServiceRoomIndex($item, $this->params->serviceRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->service_id != null) {
                $id = $request->service_id;
                $data = $this->serviceRepository->getById($id);
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
                        $deleteIds = $this->serviceRoomRepository->deleteByServiceId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->serviceRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->serviceRoomRepository->getByServiceIdAndRoomIds($id, $room_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertServiceRoomIndex($item, $this->params->serviceRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->serviceRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_room'], $e);
        }
    }
}