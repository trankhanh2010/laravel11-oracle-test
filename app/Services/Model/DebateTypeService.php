<?php

namespace App\Services\Model;

use App\DTOs\DebateTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateType\InsertDebateTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateTypeRepository;
use Illuminate\Support\Facades\Redis;

class DebateTypeService
{
    protected $debateTypeRepository;
    protected $params;
    public function __construct(DebateTypeRepository $debateTypeRepository)
    {
        $this->debateTypeRepository = $debateTypeRepository;
    }
    public function withParams(DebateTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateTypeRepository->applyJoins();
            $data = $this->debateTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->debateTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->debateTypeRepository->applyJoins();
        $data = $this->debateTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->debateTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->debateTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->debateTypeRepository->applyJoins()
            ->where('his_debate_type.id', $id);
        $data = $this->debateTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->debateTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->debateTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->debateTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->debateTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_type'], $e);
        }
    }

    public function createDebateType($request)
    {
        try {
            $data = $this->debateTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDebateTypeIndex($data, $this->params->debateTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_type'], $e);
        }
    }

    public function updateDebateType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDebateTypeIndex($data, $this->params->debateTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_type'], $e);
        }
    }

    public function deleteDebateType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->debateTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_type'], $e);
        }
    }
}
