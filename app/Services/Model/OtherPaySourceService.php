<?php

namespace App\Services\Model;

use App\DTOs\OtherPaySourceDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\OtherPaySource\InsertOtherPaySourceIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\OtherPaySourceRepository;
use Illuminate\Support\Facades\Redis;

class OtherPaySourceService
{
    protected $otherPaySourceRepository;
    protected $params;
    public function __construct(OtherPaySourceRepository $otherPaySourceRepository)
    {
        $this->otherPaySourceRepository = $otherPaySourceRepository;
    }
    public function withParams(OtherPaySourceDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->otherPaySourceRepository->applyJoins();
            $data = $this->otherPaySourceRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->otherPaySourceRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->otherPaySourceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->otherPaySourceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->otherPaySourceRepository->applyJoins();
        $data = $this->otherPaySourceRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->otherPaySourceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->otherPaySourceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->otherPaySourceRepository->applyJoins()
            ->where('his_other_pay_source.id', $id);
        $data = $this->otherPaySourceRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->otherPaySourceName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->otherPaySourceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->otherPaySourceName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->otherPaySourceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }

    public function createOtherPaySource($request)
    {
        try {
            $data = $this->otherPaySourceRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertOtherPaySourceIndex($data, $this->params->otherPaySourceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->otherPaySourceName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }

    public function updateOtherPaySource($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->otherPaySourceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->otherPaySourceRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertOtherPaySourceIndex($data, $this->params->otherPaySourceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->otherPaySourceName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }

    public function deleteOtherPaySource($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->otherPaySourceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->otherPaySourceRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->otherPaySourceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->otherPaySourceName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['other_pay_source'], $e);
        }
    }
}
