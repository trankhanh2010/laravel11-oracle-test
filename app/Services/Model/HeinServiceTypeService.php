<?php

namespace App\Services\Model;

use App\DTOs\HeinServiceTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\HeinServiceType\InsertHeinServiceTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\HeinServiceTypeRepository;
use Illuminate\Support\Facades\Redis;

class HeinServiceTypeService
{
    protected $heinServiceTypeRepository;
    protected $params;
    public function __construct(HeinServiceTypeRepository $heinServiceTypeRepository)
    {
        $this->heinServiceTypeRepository = $heinServiceTypeRepository;
    }
    public function withParams(HeinServiceTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->heinServiceTypeRepository->applyJoins();
            $data = $this->heinServiceTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->heinServiceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->heinServiceTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->heinServiceTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->heinServiceTypeRepository->applyJoins();
        $data = $this->heinServiceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->heinServiceTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->heinServiceTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->heinServiceTypeRepository->applyJoins()
            ->where('his_hein_service_type.id', $id);
        $data = $this->heinServiceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->heinServiceTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->heinServiceTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->heinServiceTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->heinServiceTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
    public function deleteHeinServiceType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->heinServiceTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->heinServiceTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->heinServiceTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->heinServiceTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['hein_service_type'], $e);
        }
    }
}
