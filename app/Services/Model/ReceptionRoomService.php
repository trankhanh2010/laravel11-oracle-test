<?php

namespace App\Services\Model;

use App\DTOs\ReceptionRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ReceptionRoom\InsertReceptionRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ReceptionRoomRepository;
use Illuminate\Support\Facades\Redis;

class ReceptionRoomService
{
    protected $receptionRoomRepository;
    protected $params;
    public function __construct(ReceptionRoomRepository $receptionRoomRepository)
    {
        $this->receptionRoomRepository = $receptionRoomRepository;
    }
    public function withParams(ReceptionRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->receptionRoomRepository->applyJoins();
            $data = $this->receptionRoomRepository->applyWith($data);
            $data = $this->receptionRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->receptionRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->receptionRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->receptionRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['reception_room'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->receptionRoomRepository->applyJoins();
        $data = $this->receptionRoomRepository->applyWith($data);
        $data = $this->receptionRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->receptionRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->receptionRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->receptionRoomRepository->applyJoins()
            ->where('his_reception_room.id', $id);
        $data = $this->receptionRoomRepository->applyWith($data);
        $data = $this->receptionRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->receptionRoomName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->receptionRoomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['reception_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->receptionRoomName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->receptionRoomName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['reception_room'], $e);
        }
    }

    public function createReceptionRoom($request)
    {
        try {
            $data = $this->receptionRoomRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertReceptionRoomIndex($data, $this->params->receptionRoomName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->receptionRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['reception_room'], $e);
        }
    }

    public function updateReceptionRoom($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->receptionRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->receptionRoomRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertReceptionRoomIndex($data, $this->params->receptionRoomName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->receptionRoomName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['reception_room'], $e);
        }
    }

    public function deleteReceptionRoom($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->receptionRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->receptionRoomRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->receptionRoomName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->receptionRoomName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['reception_room'], $e);
        }
    }
}
