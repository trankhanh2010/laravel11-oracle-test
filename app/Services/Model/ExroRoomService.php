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
use Illuminate\Support\Facades\Redis;

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
    private function getAllDataFromDatabase()
    {
        $data = $this->exroRoomRepository->applyJoins();
        $data = $this->exroRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->exroRoomRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
        $data = $this->exroRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
        $count = $data->count();
        $data = $this->exroRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->exroRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->exroRoomRepository->applyJoins()
            ->where('his_exro_room.id', $id);
        $data = $this->exroRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->exroRoomName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->exroRoomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exro_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->exroRoomName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->exroRoomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exro_room'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
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
