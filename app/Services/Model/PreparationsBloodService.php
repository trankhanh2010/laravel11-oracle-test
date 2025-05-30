<?php

namespace App\Services\Model;

use App\DTOs\PreparationsBloodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PreparationsBlood\InsertPreparationsBloodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PreparationsBloodRepository;
use Illuminate\Support\Facades\Redis;

class PreparationsBloodService
{
    protected $preparationsBloodRepository;
    protected $params;
    public function __construct(PreparationsBloodRepository $preparationsBloodRepository)
    {
        $this->preparationsBloodRepository = $preparationsBloodRepository;
    }
    public function withParams(PreparationsBloodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->preparationsBloodRepository->applyJoins();
            $data = $this->preparationsBloodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->preparationsBloodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->preparationsBloodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->preparationsBloodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->preparationsBloodRepository->applyJoins();
        $data = $this->preparationsBloodRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->preparationsBloodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->preparationsBloodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->preparationsBloodRepository->applyJoins()
            ->where('his_preparations_blood.id', $id);
        $data = $this->preparationsBloodRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->preparationsBloodName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->preparationsBloodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->preparationsBloodName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->preparationsBloodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }

    public function createPreparationsBlood($request)
    {
        try {
            $data = $this->preparationsBloodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPreparationsBloodIndex($data, $this->params->preparationsBloodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->preparationsBloodName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }

    public function updatePreparationsBlood($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->preparationsBloodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->preparationsBloodRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPreparationsBloodIndex($data, $this->params->preparationsBloodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->preparationsBloodName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }

    public function deletePreparationsBlood($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->preparationsBloodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->preparationsBloodRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->preparationsBloodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->preparationsBloodName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['preparations_blood'], $e);
        }
    }
}
