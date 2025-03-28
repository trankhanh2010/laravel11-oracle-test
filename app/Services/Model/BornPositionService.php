<?php

namespace App\Services\Model;

use App\DTOs\BornPositionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BornPosition\InsertBornPositionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BornPositionRepository;
use Illuminate\Support\Facades\Redis;

class BornPositionService 
{
    protected $bornPositionRepository;
    protected $params;
    public function __construct(BornPositionRepository $bornPositionRepository)
    {
        $this->bornPositionRepository = $bornPositionRepository;
    }
    public function withParams(BornPositionDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bornPositionRepository->applyJoins();
            $data = $this->bornPositionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bornPositionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bornPositionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bornPositionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['born_position'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->bornPositionName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->bornPositionName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->bornPositionRepository->applyJoins();
                $data = $this->bornPositionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bornPositionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bornPositionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['born_position'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->bornPositionName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->bornPositionName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->bornPositionRepository->applyJoins()
                    ->where('his_born_position.id', $id);
                $data = $this->bornPositionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['born_position'], $e);
        }
    }

    public function createBornPosition($request)
    {
        try {
            $data = $this->bornPositionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBornPositionIndex($data, $this->params->bornPositionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bornPositionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['born_position'], $e);
        }
    }

    public function updateBornPosition($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bornPositionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bornPositionRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBornPositionIndex($data, $this->params->bornPositionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bornPositionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['born_position'], $e);
        }
    }

    public function deleteBornPosition($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bornPositionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bornPositionRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bornPositionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bornPositionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['born_position'], $e);
        }
    }
}
