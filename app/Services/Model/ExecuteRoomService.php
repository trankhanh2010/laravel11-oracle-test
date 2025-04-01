<?php

namespace App\Services\Model;

use App\DTOs\ExecuteRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExecuteRoom\InsertExecuteRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExecuteRoomRepository;
use Illuminate\Support\Facades\Redis;

class ExecuteRoomService
{
    protected $executeRoomRepository;
    protected $params;
    public function __construct(ExecuteRoomRepository $executeRoomRepository)
    {
        $this->executeRoomRepository = $executeRoomRepository;
    }
    public function withParams(ExecuteRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->executeRoomRepository->applyJoins();
            $data = $this->executeRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->executeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->executeRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->executeRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->executeRoomRepository->applyJoins();
        $data = $this->executeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->executeRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->executeRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->executeRoomRepository->applyJoins()
            ->where('his_execute_room.id', $id);
        $data = $this->executeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->executeRoomName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->executeRoomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->executeRoomName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->executeRoomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }

    public function createExecuteRoom($request)
    {
        try {
            $data = $this->executeRoomRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExecuteRoomIndex($data, $this->params->executeRoomName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }

    public function updateExecuteRoom($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoomRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExecuteRoomIndex($data, $this->params->executeRoomName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoomName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }

    public function deleteExecuteRoom($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoomRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->executeRoomName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoomName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }
}
