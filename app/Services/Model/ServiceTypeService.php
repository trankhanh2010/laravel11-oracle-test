<?php

namespace App\Services\Model;

use App\DTOs\ServiceTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceType\InsertServiceTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Support\Facades\Redis;

class ServiceTypeService 
{
    protected $serviceTypeRepository;
    protected $params;
    public function __construct(ServiceTypeRepository $serviceTypeRepository)
    {
        $this->serviceTypeRepository = $serviceTypeRepository;
    }
    public function withParams(ServiceTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceTypeRepository->applyJoins();
            $data = $this->serviceTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->serviceTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->serviceTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->serviceTypeRepository->applyJoins();
                $data = $this->serviceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->serviceTypeRepository->applyTabFilter($data, $this->params->tab);
                $count = $data->count();
                $data = $this->serviceTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->serviceTypeName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->serviceTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->serviceTypeRepository->applyJoins()
                    ->where('his_service_type.id', $id);
                $data = $this->serviceTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_type'], $e);
        }
    }
    public function createServiceType($request)
    {
        try {
            $data = $this->serviceTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertServiceTypeIndex($data, $this->params->serviceTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_type'], $e);
        }
    }

    public function updateServiceType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertServiceTypeIndex($data, $this->params->serviceTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_type'], $e);
        }
    }

    public function deleteServiceType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_type'], $e);
        }
    }
}
