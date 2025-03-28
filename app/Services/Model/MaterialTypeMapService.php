<?php

namespace App\Services\Model;

use App\DTOs\MaterialTypeMapDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MaterialTypeMap\InsertMaterialTypeMapIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MaterialTypeMapRepository;
use Illuminate\Support\Facades\Redis;

class MaterialTypeMapService 
{
    protected $materialTypeMapRepository;
    protected $params;
    public function __construct(MaterialTypeMapRepository $materialTypeMapRepository)
    {
        $this->materialTypeMapRepository = $materialTypeMapRepository;
    }
    public function withParams(MaterialTypeMapDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->materialTypeMapRepository->applyJoins();
            $data = $this->materialTypeMapRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->materialTypeMapRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->materialTypeMapRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->materialTypeMapRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type_map'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->materialTypeMapName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->materialTypeMapName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->materialTypeMapRepository->applyJoins();
                $data = $this->materialTypeMapRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->materialTypeMapRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->materialTypeMapRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type_map'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->materialTypeMapName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->materialTypeMapRepository->applyJoins()
                    ->where('his_material_type_map.id', $id);
                $data = $this->materialTypeMapRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type_map'], $e);
        }
    }

    public function createMaterialTypeMap($request)
    {
        try {
            $data = $this->materialTypeMapRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMaterialTypeMapIndex($data, $this->params->materialTypeMapName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->materialTypeMapName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type_map'], $e);
        }
    }

    public function updateMaterialTypeMap($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->materialTypeMapRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->materialTypeMapRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMaterialTypeMapIndex($data, $this->params->materialTypeMapName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->materialTypeMapName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type_map'], $e);
        }
    }

    public function deleteMaterialTypeMap($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->materialTypeMapRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->materialTypeMapRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->materialTypeMapName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->materialTypeMapName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type_map'], $e);
        }
    }
}
