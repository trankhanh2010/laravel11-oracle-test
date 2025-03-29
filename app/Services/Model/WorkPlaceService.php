<?php

namespace App\Services\Model;

use App\DTOs\WorkPlaceDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\WorkPlace\InsertWorkPlaceIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\WorkPlaceRepository;
use Illuminate\Support\Facades\Redis;

class WorkPlaceService 
{
    protected $workPlaceRepository;
    protected $params;
    public function __construct(WorkPlaceRepository $workPlaceRepository)
    {
        $this->workPlaceRepository = $workPlaceRepository;
    }
    public function withParams(WorkPlaceDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->workPlaceRepository->applyJoins();
            $data = $this->workPlaceRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->workPlaceRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->workPlaceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->workPlaceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['work_place'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->workPlaceName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->workPlaceName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->workPlaceRepository->applyJoins();
                $data = $this->workPlaceRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->workPlaceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->workPlaceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['work_place'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->workPlaceName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->workPlaceName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->workPlaceRepository->applyJoins()
                    ->where('his_work_place.id', $id);
                $data = $this->workPlaceRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['work_place'], $e);
        }
    }

    public function createWorkPlace($request)
    {
        try {
            $data = $this->workPlaceRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertWorkPlaceIndex($data, $this->params->workPlaceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->workPlaceName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['work_place'], $e);
        }
    }

    public function updateWorkPlace($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->workPlaceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->workPlaceRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertWorkPlaceIndex($data, $this->params->workPlaceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->workPlaceName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['work_place'], $e);
        }
    }

    public function deleteWorkPlace($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->workPlaceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->workPlaceRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->workPlaceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->workPlaceName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['work_place'], $e);
        }
    }
}
