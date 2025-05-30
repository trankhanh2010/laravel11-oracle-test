<?php

namespace App\Services\Model;

use App\DTOs\SuimIndexDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SuimIndex\InsertSuimIndexIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SuimIndexRepository;
use Illuminate\Support\Facades\Redis;

class SuimIndexService
{
    protected $suimIndexRepository;
    protected $params;
    public function __construct(SuimIndexRepository $suimIndexRepository)
    {
        $this->suimIndexRepository = $suimIndexRepository;
    }
    public function withParams(SuimIndexDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->suimIndexRepository->applyJoins();
            $data = $this->suimIndexRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->suimIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->suimIndexRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->suimIndexRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->suimIndexRepository->applyJoins();
        $data = $this->suimIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->suimIndexRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->suimIndexRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->suimIndexRepository->applyJoins()
            ->where('his_suim_index.id', $id);
        $data = $this->suimIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->suimIndexName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->suimIndexName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->suimIndexName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->suimIndexName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
    public function createSuimIndex($request)
    {
        try {
            $data = $this->suimIndexRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertSuimIndexIndex($data, $this->params->suimIndexName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->suimIndexName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }

    public function updateSuimIndex($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->suimIndexRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->suimIndexRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertSuimIndexIndex($data, $this->params->suimIndexName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->suimIndexName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }

    public function deleteSuimIndex($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->suimIndexRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->suimIndexRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->suimIndexName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->suimIndexName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['suim_index'], $e);
        }
    }
}
