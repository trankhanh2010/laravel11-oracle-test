<?php

namespace App\Services\Model;

use App\DTOs\ReligionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Religion\InsertReligionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ReligionRepository;
use Illuminate\Support\Facades\Redis;

class ReligionService 
{
    protected $religionRepository;
    protected $params;
    public function __construct(ReligionRepository $religionRepository)
    {
        $this->religionRepository = $religionRepository;
    }
    public function withParams(ReligionDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->religionRepository->applyJoins();
            $data = $this->religionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->religionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->religionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->religionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['religion'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->religionName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->religionName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->religionRepository->applyJoins();
                $data = $this->religionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->religionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->religionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['religion'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->religionName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->religionRepository->applyJoins()
                    ->where('sda_religion.id', $id);
                $data = $this->religionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['religion'], $e);
        }
    }

    public function createReligion($request)
    {
        try {
            $data = $this->religionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertReligionIndex($data, $this->params->religionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->religionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['religion'], $e);
        }
    }

    public function updateReligion($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->religionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->religionRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertReligionIndex($data, $this->params->religionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->religionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['religion'], $e);
        }
    }

    public function deleteReligion($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->religionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->religionRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->religionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->religionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['religion'], $e);
        }
    }
}
