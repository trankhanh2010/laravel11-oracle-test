<?php

namespace App\Services\Model;

use App\DTOs\ServiceUnitDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceUnit\InsertServiceUnitIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceUnitRepository;
use Illuminate\Support\Facades\Redis;

class ServiceUnitService
{
    protected $serviceUnitRepository;
    protected $params;
    public function __construct(ServiceUnitRepository $serviceUnitRepository)
    {
        $this->serviceUnitRepository = $serviceUnitRepository;
    }
    public function withParams(ServiceUnitDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceUnitRepository->applyJoins();
            $data = $this->serviceUnitRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceUnitRepository->applyJoins();
        $data = $this->serviceUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->serviceUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceUnitRepository->applyJoins()
            ->where('his_service_unit.id', $id);
        $data = $this->serviceUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->serviceUnitName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceUnitName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->serviceUnitName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceUnitName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }

    public function createServiceUnit($request)
    {
        try {
            $data = $this->serviceUnitRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceUnitIndex($data, $this->params->serviceUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceUnitName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }

    public function updateServiceUnit($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceUnitRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceUnitIndex($data, $this->params->serviceUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceUnitName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }

    public function deleteServiceUnit($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceUnitRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceUnitName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_unit'], $e);
        }
    }
}
