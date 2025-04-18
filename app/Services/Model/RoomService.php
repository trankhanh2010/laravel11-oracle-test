<?php

namespace App\Services\Model;

use App\DTOs\RoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Room\InsertRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\Redis;

class RoomService
{
    protected $roomRepository;
    protected $params;
    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }
    public function withParams(RoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->roomRepository->applyJoins();
            $data = $this->roomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->roomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->roomRepository->applyDepartmentIdFilter($data, $this->params->departmentId);
            $data = $this->roomRepository->applyRoomTypeIdFilter($data, $this->params->roomTypeId);
            $count = $data->count();
            $data = $this->roomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->roomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->roomRepository->applyJoins();
        $data = $this->roomRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->roomRepository->applyDepartmentIdFilter($data, $this->params->departmentId);
        $data = $this->roomRepository->applyRoomTypeIdFilter($data, $this->params->roomTypeId);
        $count = $data->count();
        $data = $this->roomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->roomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->roomRepository->applyJoins()
            ->where('his_room.id', $id);
        $data = $this->roomRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->roomName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->roomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->roomName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->roomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room'], $e);
        }
    }
}
