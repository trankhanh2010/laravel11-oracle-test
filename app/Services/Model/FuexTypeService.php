<?php

namespace App\Services\Model;

use App\DTOs\FuexTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\FuexType\InsertFuexTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\FuexTypeRepository;
use Illuminate\Support\Facades\Redis;

class FuexTypeService 
{
    protected $fuexTypeRepository;
    protected $params;
    public function __construct(FuexTypeRepository $fuexTypeRepository)
    {
        $this->fuexTypeRepository = $fuexTypeRepository;
    }
    public function withParams(FuexTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->fuexTypeRepository->applyJoins();
            $data = $this->fuexTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->fuexTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->fuexTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->fuexTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['fuex_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->fuexTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->fuexTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->fuexTypeRepository->applyJoins();
                $data = $this->fuexTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->fuexTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->fuexTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['fuex_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->fuexTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->fuexTypeRepository->applyJoins()
                    ->where('his_fuex_type.id', $id);
                $data = $this->fuexTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['fuex_type'], $e);
        }
    }
    public function createFuexType($request)
    {
        try {
            $data = $this->fuexTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertFuexTypeIndex($data, $this->params->fuexTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->fuexTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['fuex_type'], $e);
        }
    }

    public function updateFuexType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->fuexTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->fuexTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertFuexTypeIndex($data, $this->params->fuexTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->fuexTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['fuex_type'], $e);
        }
    }

    public function deleteFuexType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->fuexTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->fuexTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->fuexTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->fuexTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['fuex_type'], $e);
        }
    }
}
