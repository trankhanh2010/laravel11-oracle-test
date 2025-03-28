<?php

namespace App\Services\Model;

use App\DTOs\UnlimitReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\UnlimitReason\InsertUnlimitReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\UnlimitReasonRepository;
use Illuminate\Support\Facades\Redis;

class UnlimitReasonService 
{
    protected $unlimitReasonRepository;
    protected $params;
    public function __construct(UnlimitReasonRepository $unlimitReasonRepository)
    {
        $this->unlimitReasonRepository = $unlimitReasonRepository;
    }
    public function withParams(UnlimitReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->unlimitReasonRepository->applyJoins();
            $data = $this->unlimitReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->unlimitReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->unlimitReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->unlimitReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['unlimit_reason'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->unlimitReasonName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->unlimitReasonName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->unlimitReasonRepository->applyJoins();
                $data = $this->unlimitReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->unlimitReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->unlimitReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['unlimit_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->unlimitReasonName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->unlimitReasonRepository->applyJoins()
                    ->where('his_unlimit_reason.id', $id);
                $data = $this->unlimitReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['unlimit_reason'], $e);
        }
    }

    public function createUnlimitReason($request)
    {
        try {
            $data = $this->unlimitReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertUnlimitReasonIndex($data, $this->params->unlimitReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->unlimitReasonName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['unlimit_reason'], $e);
        }
    }

    public function updateUnlimitReason($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->unlimitReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->unlimitReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertUnlimitReasonIndex($data, $this->params->unlimitReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->unlimitReasonName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['unlimit_reason'], $e);
        }
    }

    public function deleteUnlimitReason($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->unlimitReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->unlimitReasonRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->unlimitReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->unlimitReasonName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['unlimit_reason'], $e);
        }
    }
}
