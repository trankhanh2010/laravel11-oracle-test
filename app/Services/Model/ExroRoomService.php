<?php

namespace App\Services\Model;

use App\DTOs\ExroRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExroRoom\InsertExroRoomIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\ExecuteRoomRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExroRoomRepository;
use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\DB;

class ExroRoomService
{
    protected $exroRoomRepository;
    protected $roomRepository;
    protected $executeRoomRepository;
    protected $params;
    public function __construct(ExroRoomRepository $exroRoomRepository, RoomRepository $roomRepository, ExecuteRoomRepository $executeRoomRepository)
    {
        $this->exroRoomRepository = $exroRoomRepository;
        $this->roomRepository = $roomRepository;
        $this->executeRoomRepository = $executeRoomRepository;
    }
    public function withParams(ExroRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->exroRoomRepository->applyJoins();
            $data = $this->exroRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->exroRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->exroRoomRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->exroRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
            $count = $data->count();
            $data = $this->exroRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->exroRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exro_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->exroRoomName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_room_id_' . $this->params->roomId . '_execute_room_id_' . $this->params->executeRoomId . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->exroRoomRepository->applyJoins();
                $data = $this->exroRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->exroRoomRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
                $data = $this->exroRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
                $count = $data->count();
                $data = $this->exroRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->exroRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exro_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->exroRoomName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->exroRoomRepository->applyJoins()
                    ->where('his_exro_room.id', $id);
                $data = $this->exroRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exro_room'], $e);
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
            'is_hold_order' => $request->is_hold_order,
            'is_allow_request' => $request->is_allow_request,
            'is_priority_require' => $request->is_priority_require,
        ];
    }
    public function createExroRoom($request)
    {
        try {
            if ($request->execute_room_id != null) {
                $id = $request->execute_room_id;
                $data = $this->executeRoomRepository->getById($id);
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
                        $deleteIds = $this->exroRoomRepository->deleteByExecuteRoomId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->exroRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->exroRoomRepository->getByExecuteRoomIdAndRoomIds($id, $room_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertExroRoomIndex($item, $this->params->exroRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->room_id != null) {
                $id = $request->room_id;
                $data = $this->roomRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->execute_room_ids !== null) {
                        $execute_room_ids_arr = explode(',', $request->execute_room_ids);
                        foreach ($execute_room_ids_arr as $key => $item) {
                            $execute_room_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->execute_rooms()->sync($execute_room_ids_arr_data);
                    } else {
                        $deleteIds = $this->exroRoomRepository->deleteByRoomId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->exroRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->exroRoomRepository->getByRoomIdAndExecuteRoomIds($id, $execute_room_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertExroRoomIndex($item, $this->params->exroRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->exroRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exro_room'], $e);
        }
    }
}