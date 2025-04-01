<?php

namespace App\Services\Model;

use App\DTOs\TestIndexGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestIndexGroup\InsertTestIndexGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestIndexGroupRepository;
use Illuminate\Support\Facades\Redis;

class TestIndexGroupService
{
    protected $testIndexGroupRepository;
    protected $params;
    public function __construct(TestIndexGroupRepository $testIndexGroupRepository)
    {
        $this->testIndexGroupRepository = $testIndexGroupRepository;
    }
    public function withParams(TestIndexGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testIndexGroupRepository->applyJoins();
            $data = $this->testIndexGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testIndexGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->testIndexGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testIndexGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->testIndexGroupRepository->applyJoins();
        $data = $this->testIndexGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->testIndexGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->testIndexGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->testIndexGroupRepository->applyJoins()
            ->where('his_test_index_group.id', $id);
        $data = $this->testIndexGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->testIndexGroupName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->testIndexGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->testIndexGroupName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->testIndexGroupName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }

    public function createTestIndexGroup($request)
    {
        try {
            $data = $this->testIndexGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexGroupIndex($data, $this->params->testIndexGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }

    public function updateTestIndexGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexGroupIndex($data, $this->params->testIndexGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }

    public function deleteTestIndexGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexGroupRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->testIndexGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }
}
