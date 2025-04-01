<?php

namespace App\Services\Model;

use App\DTOs\PtttMethodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PtttMethod\InsertPtttMethodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PtttMethodRepository;
use Illuminate\Support\Facades\Redis;

class PtttMethodService
{
    protected $ptttMethodRepository;
    protected $params;
    public function __construct(PtttMethodRepository $ptttMethodRepository)
    {
        $this->ptttMethodRepository = $ptttMethodRepository;
    }
    public function withParams(PtttMethodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->ptttMethodRepository->applyJoins();
            $data = $this->ptttMethodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ptttMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ptttMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ptttMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_method'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->ptttMethodRepository->applyJoins();
        $data = $this->ptttMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->ptttMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->ptttMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->ptttMethodRepository->applyJoins()
            ->where('his_pttt_method.id', $id);
        $data = $this->ptttMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->ptttMethodName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ptttMethodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_method'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->ptttMethodName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ptttMethodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_method'], $e);
        }
    }

    public function createPtttMethod($request)
    {
        try {
            $data = $this->ptttMethodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPtttMethodIndex($data, $this->params->ptttMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttMethodName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_method'], $e);
        }
    }

    public function updatePtttMethod($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttMethodRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPtttMethodIndex($data, $this->params->ptttMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttMethodName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_method'], $e);
        }
    }

    public function deletePtttMethod($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttMethodRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ptttMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttMethodName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_method'], $e);
        }
    }
}
