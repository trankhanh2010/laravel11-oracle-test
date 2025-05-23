<?php

namespace App\Services\Model;

use App\DTOs\ServiceConditionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceCondition\InsertServiceConditionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceConditionRepository;
use Illuminate\Support\Facades\Redis;

class ServiceConditionService
{
    protected $serviceConditionRepository;
    protected $params;
    public function __construct(ServiceConditionRepository $serviceConditionRepository)
    {
        $this->serviceConditionRepository = $serviceConditionRepository;
    }
    public function withParams(ServiceConditionDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceConditionRepository->applyJoins();
            $data = $this->serviceConditionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceConditionRepository->applyServiceIdFilter($data, $this->params->serviceId);
            $count = $data->count();
            $data = $this->serviceConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceConditionRepository->applyJoins();
        $data = $this->serviceConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->serviceConditionRepository->applyServiceIdFilter($data, $this->params->serviceId);
        $count = $data->count();
        $data = $this->serviceConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceConditionRepository->applyJoins()
            ->where('his_service_condition.id', $id);
        $data = $this->serviceConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->serviceConditionName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceConditionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->serviceConditionName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceConditionName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }

    public function createServiceCondition($request)
    {
        try {
            $data = $this->serviceConditionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceConditionIndex($data, $this->params->serviceConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceConditionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }

    public function updateServiceCondition($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceConditionRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceConditionIndex($data, $this->params->serviceConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceConditionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }

    public function deleteServiceCondition($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceConditionRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceConditionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }
}
