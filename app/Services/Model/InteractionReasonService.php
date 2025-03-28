<?php

namespace App\Services\Model;

use App\DTOs\InteractionReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\InteractionReason\InsertInteractionReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\InteractionReasonRepository;
use Illuminate\Support\Facades\Redis;

class InteractionReasonService 
{
    protected $interactionReasonRepository;
    protected $params;
    public function __construct(InteractionReasonRepository $interactionReasonRepository)
    {
        $this->interactionReasonRepository = $interactionReasonRepository;
    }
    public function withParams(InteractionReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->interactionReasonRepository->applyJoins();
            $data = $this->interactionReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->interactionReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->interactionReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->interactionReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['interaction_reason'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->interactionReasonName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->interactionReasonName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->interactionReasonRepository->applyJoins();
                $data = $this->interactionReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->interactionReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->interactionReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['interaction_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->interactionReasonName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->interactionReasonRepository->applyJoins()
                    ->where('his_interaction_reason.id', $id);
                $data = $this->interactionReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['interaction_reason'], $e);
        }
    }

    public function createInteractionReason($request)
    {
        try {
            $data = $this->interactionReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertInteractionReasonIndex($data, $this->params->interactionReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->interactionReasonName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['interaction_reason'], $e);
        }
    }

    public function updateInteractionReason($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->interactionReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->interactionReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertInteractionReasonIndex($data, $this->params->interactionReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->interactionReasonName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['interaction_reason'], $e);
        }
    }

    public function deleteInteractionReason($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->interactionReasonRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->interactionReasonRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->interactionReasonName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->interactionReasonName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['interaction_reason'], $e);
        }
    }
}
