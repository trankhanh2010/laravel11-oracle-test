<?php

namespace App\Services\Model;

use App\DTOs\NationalDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\National\InsertNationalIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\NationalRepository;
use Illuminate\Support\Facades\Redis;

class NationalService 
{
    protected $nationalRepository;
    protected $params;
    public function __construct(NationalRepository $nationalRepository)
    {
        $this->nationalRepository = $nationalRepository;
    }
    public function withParams(NationalDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->nationalRepository->applyJoins();
            $data = $this->nationalRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->nationalRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->nationalRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->nationalRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['national'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->nationalName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->nationalName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->nationalRepository->applyJoins();
                $data = $this->nationalRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->nationalRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->nationalRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['national'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->nationalName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->nationalRepository->applyJoins()
                    ->where('sda_national.id', $id);
                $data = $this->nationalRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['national'], $e);
        }
    }

    public function createNational($request)
    {
        try {
            $data = $this->nationalRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertNationalIndex($data, $this->params->nationalName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->nationalName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['national'], $e);
        }
    }

    public function updateNational($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->nationalRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->nationalRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertNationalIndex($data, $this->params->nationalName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->nationalName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['national'], $e);
        }
    }

    public function deleteNational($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->nationalRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->nationalRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->nationalName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->nationalName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['national'], $e);
        }
    }
}
