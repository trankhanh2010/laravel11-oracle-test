<?php

namespace App\Services\Model;

use App\DTOs\RelationDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Relation\InsertRelationIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RelationRepository;
use Illuminate\Support\Facades\Redis;

class RelationService 
{
    protected $relationRepository;
    protected $params;
    public function __construct(RelationRepository $relationRepository)
    {
        $this->relationRepository = $relationRepository;
    }
    public function withParams(RelationDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->relationRepository->applyJoins();
            $data = $this->relationRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->relationRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->relationRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->relationRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['relation'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->relationName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->relationName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->relationRepository->applyJoins();
                $data = $this->relationRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->relationRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->relationRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['relation'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->relationName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->relationRepository->applyJoins()
                    ->where('emr_relation.id', $id);
                $data = $this->relationRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['relation'], $e);
        }
    }

    public function createRelation($request)
    {
        try {
            $data = $this->relationRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertRelationIndex($data, $this->params->relationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->relationName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['relation'], $e);
        }
    }

    public function updateRelation($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->relationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->relationRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertRelationIndex($data, $this->params->relationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->relationName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['relation'], $e);
        }
    }

    public function deleteRelation($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->relationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->relationRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->relationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->relationName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['relation'], $e);
        }
    }
}
