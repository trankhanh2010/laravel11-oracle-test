<?php

namespace App\Services\Model;

use App\DTOs\ServiceReqTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReqType\InsertServiceReqTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceReqTypeRepository;
use Illuminate\Support\Facades\Redis;

class ServiceReqTypeService
{
    protected $serviceReqTypeRepository;
    protected $params;
    public function __construct(ServiceReqTypeRepository $serviceReqTypeRepository)
    {
        $this->serviceReqTypeRepository = $serviceReqTypeRepository;
    }
    public function withParams(ServiceReqTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceReqTypeRepository->applyJoins();
            $data = $this->serviceReqTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceReqTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceReqTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceReqTypeRepository->applyJoins();
        $data = $this->serviceReqTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->serviceReqTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceReqTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceReqTypeRepository->applyJoins()
            ->where('id', $id);
        $data = $this->serviceReqTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->serviceReqTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceReqTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->serviceReqTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceReqTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
    public function createServiceReqType($request)
    {
        try {
            $data = $this->serviceReqTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceReqTypeIndex($data, $this->params->serviceReqTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }

    public function updateServiceReqType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceReqTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceReqTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceReqTypeIndex($data, $this->params->serviceReqTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }

    public function deleteServiceReqType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceReqTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceReqTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceReqTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
}
