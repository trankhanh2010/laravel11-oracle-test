<?php

namespace App\Services\Model;

use App\DTOs\CancelReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\CancelReason\InsertCancelReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CancelReasonRepository;
use Illuminate\Support\Facades\Redis;

class CancelReasonService 
{
    protected $cancelReasonRepository;
    protected $params;
    public function __construct(CancelReasonRepository $cancelReasonRepository)
    {
        $this->cancelReasonRepository = $cancelReasonRepository;
    }
    public function withParams(CancelReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->cancelReasonRepository->applyJoins();
            $data = $this->cancelReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->cancelReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->cancelReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->cancelReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cancel_reason'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->cancelReasonName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->cancelReasonName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->cancelReasonRepository->applyJoins();
                $data = $this->cancelReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->cancelReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->cancelReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cancel_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->cancelReasonName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->cancelReasonRepository->applyJoins()
                    ->where('his_cancel_reason.id', $id);
                $data = $this->cancelReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cancel_reason'], $e);
        }
    }

    public function createCancelReason($request)
    {
        try {
            $data = $this->cancelReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCancelReasonIndex($data, $this->params->cancelReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->cancelReasonName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cancel_reason'], $e);
        }
    }

    public function updateCancelReason($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->cancelReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->cancelReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCancelReasonIndex($data, $this->params->cancelReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->cancelReasonName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cancel_reason'], $e);
        }
    }

    public function deleteCancelReason($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->cancelReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->cancelReasonRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->cancelReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->cancelReasonName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cancel_reason'], $e);
        }
    }
}
