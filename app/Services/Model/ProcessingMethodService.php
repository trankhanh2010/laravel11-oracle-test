<?php

namespace App\Services\Model;

use App\DTOs\ProcessingMethodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ProcessingMethod\InsertProcessingMethodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ProcessingMethodRepository;
use Illuminate\Support\Facades\Redis;

class ProcessingMethodService 
{
    protected $processingMethodRepository;
    protected $params;
    public function __construct(ProcessingMethodRepository $processingMethodRepository)
    {
        $this->processingMethodRepository = $processingMethodRepository;
    }
    public function withParams(ProcessingMethodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->processingMethodRepository->applyJoins();
            $data = $this->processingMethodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->processingMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->processingMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->processingMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['processing_method'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->processingMethodName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->processingMethodName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->processingMethodRepository->applyJoins();
                $data = $this->processingMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->processingMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->processingMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['processing_method'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->processingMethodName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->processingMethodRepository->applyJoins()
                    ->where('his_processing_method.id', $id);
                $data = $this->processingMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['processing_method'], $e);
        }
    }

    public function createProcessingMethod($request)
    {
        try {
            $data = $this->processingMethodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertProcessingMethodIndex($data, $this->params->processingMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->processingMethodName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['processing_method'], $e);
        }
    }

    public function updateProcessingMethod($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->processingMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->processingMethodRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertProcessingMethodIndex($data, $this->params->processingMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->processingMethodName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['processing_method'], $e);
        }
    }

    public function deleteProcessingMethod($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->processingMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->processingMethodRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->processingMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->processingMethodName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['processing_method'], $e);
        }
    }
}
