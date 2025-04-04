<?php

namespace App\Services\Model;

use App\DTOs\IcdGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\IcdGroup\InsertIcdGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\IcdGroupRepository;
use Illuminate\Support\Facades\Redis;

class IcdGroupService
{
    protected $icdGroupRepository;
    protected $params;
    public function __construct(IcdGroupRepository $icdGroupRepository)
    {
        $this->icdGroupRepository = $icdGroupRepository;
    }
    public function withParams(IcdGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->icdGroupRepository->applyJoins();
            $data = $this->icdGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->icdGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->icdGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->icdGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_group'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->icdGroupRepository->applyJoins();
        $data = $this->icdGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->icdGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->icdGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->icdGroupRepository->applyJoins()
            ->where('his_icd_group.id', $id);
        $data = $this->icdGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->icdGroupName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->icdGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->icdGroupName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->icdGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_group'], $e);
        }
    }
    public function createIcdGroup($request)
    {
        try {
            $data = $this->icdGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertIcdGroupIndex($data, $this->params->icdGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_group'], $e);
        }
    }

    public function updateIcdGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icdGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->icdGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertIcdGroupIndex($data, $this->params->icdGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_group'], $e);
        }
    }

    public function deleteIcdGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icdGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->icdGroupRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->icdGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_group'], $e);
        }
    }
}
