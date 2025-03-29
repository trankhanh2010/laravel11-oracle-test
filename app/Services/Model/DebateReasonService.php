<?php

namespace App\Services\Model;

use App\DTOs\DebateReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateReason\InsertDebateReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateReasonRepository;
use Illuminate\Support\Facades\Redis;

class DebateReasonService 
{
    protected $debateReasonRepository;
    protected $params;
    public function __construct(DebateReasonRepository $debateReasonRepository)
    {
        $this->debateReasonRepository = $debateReasonRepository;
    }
    public function withParams(DebateReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateReasonRepository->applyJoins();
            $data = $this->debateReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->debateReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_reason'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->debateReasonName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->debateReasonName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->debateReasonRepository->applyJoins();
                $data = $this->debateReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->debateReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->debateReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->debateReasonName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->debateReasonName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->debateReasonRepository->applyJoins()
                    ->where('his_debate_reason.id', $id);
                $data = $this->debateReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_reason'], $e);
        }
    }

    public function createDebateReason($request)
    {
        try {
            $data = $this->debateReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDebateReasonIndex($data, $this->params->debateReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateReasonName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_reason'], $e);
        }
    }

    public function updateDebateReason($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDebateReasonIndex($data, $this->params->debateReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateReasonName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_reason'], $e);
        }
    }

    public function deleteDebateReason($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateReasonRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->debateReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateReasonName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_reason'], $e);
        }
    }
}
