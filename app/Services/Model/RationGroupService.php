<?php

namespace App\Services\Model;

use App\DTOs\RationGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\RationGroup\InsertRationGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RationGroupRepository;
use Illuminate\Support\Facades\Redis;

class RationGroupService
{
    protected $rationGroupRepository;
    protected $params;
    public function __construct(RationGroupRepository $rationGroupRepository)
    {
        $this->rationGroupRepository = $rationGroupRepository;
    }
    public function withParams(RationGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->rationGroupRepository->applyJoins();
            $data = $this->rationGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->rationGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->rationGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->rationGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->rationGroupRepository->applyJoins();
        $data = $this->rationGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->rationGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->rationGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->rationGroupRepository->applyJoins()
            ->where('his_ration_group.id', $id);
        $data = $this->rationGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->rationGroupName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->rationGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->rationGroupName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->rationGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }

    public function createRationGroup($request)
    {
        try {
            $data = $this->rationGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertRationGroupIndex($data, $this->params->rationGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }

    public function updateRationGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->rationGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->rationGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertRationGroupIndex($data, $this->params->rationGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }

    public function deleteRationGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->rationGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->rationGroupRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->rationGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }
}
