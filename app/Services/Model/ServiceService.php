<?php

namespace App\Services\Model;

use App\DTOs\ServiceDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Service\InsertServiceIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Facades\Redis;

class ServiceService
{
    protected $serviceRepository;
    protected $params;
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->serviceRepository = $serviceRepository;
    }
    public function withParams(ServiceDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceRepository->applyJoins();
            $data = $this->serviceRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceRepository->applyServiceTypeIdFilter($data, $this->params->serviceTypeId);
            $count = $data->count();
            $data = $this->serviceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceRepository->applyJoins();
        $data = $this->serviceRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->serviceRepository->applyServiceTypeIdFilter($data, $this->params->serviceTypeId);
        $count = $data->count();
        $data = $this->serviceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceRepository->applyJoins()
            ->where('his_service.id', $id);
        $data = $this->serviceRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->serviceName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->serviceName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service'], $e);
        }
    }

    public function createService($request)
    {
        try {
            $data = $this->serviceRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceIndex($data, $this->params->serviceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service'], $e);
        }
    }

    public function updateService($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceIndex($data, $this->params->serviceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service'], $e);
        }
    }

    public function deleteService($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service'], $e);
        }
    }
}
