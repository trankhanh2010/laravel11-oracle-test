<?php

namespace App\Services\Model;

use App\DTOs\BranchDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Branch\InsertBranchIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BranchRepository;
use Illuminate\Support\Facades\Redis;

class BranchService
{
    protected $branchRepository;
    protected $params;
    public function __construct(BranchRepository $branchRepository)
    {
        $this->branchRepository = $branchRepository;
    }
    public function withParams(BranchDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->branchRepository->applyJoins();
            $data = $this->branchRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->branchRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->branchRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->branchRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['branch'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->branchRepository->applyJoins();
        $data = $this->branchRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->branchRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->branchRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->branchRepository->applyJoins()
            ->where('his_branch.id', $id);
        $data = $this->branchRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->branchName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->branchName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['branch'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->branchName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->branchName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['branch'], $e);
        }
    }

    public function createBranch($request)
    {
        try {
            $data = $this->branchRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBranchIndex($data, $this->params->branchName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->branchName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['branch'], $e);
        }
    }

    public function updateBranch($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->branchRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->branchRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertBranchIndex($data, $this->params->branchName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->branchName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['branch'], $e);
        }
    }

    public function deleteBranch($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->branchRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->branchRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->branchName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->branchName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['branch'], $e);
        }
    }
}
