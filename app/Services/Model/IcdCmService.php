<?php

namespace App\Services\Model;

use App\DTOs\IcdCmDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\IcdCm\InsertIcdCmIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\IcdCmRepository;
use Illuminate\Support\Facades\Redis;

class IcdCmService 
{
    protected $icdCmRepository;
    protected $params;
    public function __construct(IcdCmRepository $icdCmRepository)
    {
        $this->icdCmRepository = $icdCmRepository;
    }
    public function withParams(IcdCmDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->icdCmRepository->applyJoins();
            $data = $this->icdCmRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->icdCmRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->icdCmRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->icdCmRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_cm'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->icdCmName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->icdCmName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->icdCmRepository->applyJoins();
                $data = $this->icdCmRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->icdCmRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->icdCmRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_cm'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->icdCmName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->icdCmRepository->applyJoins()
                    ->where('his_icd_cm.id', $id);
                $data = $this->icdCmRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_cm'], $e);
        }
    }

    public function createIcdCm($request)
    {
        try {
            $data = $this->icdCmRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertIcdCmIndex($data, $this->params->icdCmName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdCmName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_cm'], $e);
        }
    }

    public function updateIcdCm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icdCmRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->icdCmRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertIcdCmIndex($data, $this->params->icdCmName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdCmName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_cm'], $e);
        }
    }

    public function deleteIcdCm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->icdCmRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->icdCmRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->icdCmName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->icdCmName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['icd_cm'], $e);
        }
    }
}
