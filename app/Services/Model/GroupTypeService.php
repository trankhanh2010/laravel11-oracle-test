<?php

namespace App\Services\Model;

use App\DTOs\GroupTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\GroupType\InsertGroupTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\GroupTypeRepository;
use Illuminate\Support\Facades\Redis;

class GroupTypeService
{
    protected $groupTypeRepository;
    protected $params;
    public function __construct(GroupTypeRepository $groupTypeRepository)
    {
        $this->groupTypeRepository = $groupTypeRepository;
    }
    public function withParams(GroupTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->groupTypeRepository->applyJoins();
            $data = $this->groupTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->groupTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->groupTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->groupTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->groupTypeRepository->applyJoins();
        $data = $this->groupTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->groupTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->groupTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->groupTypeRepository->applyJoins()
            ->where('sda_group_type.id', $id);
        $data = $this->groupTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->groupTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->groupTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->groupTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->groupTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group_type'], $e);
        }
    }

    public function createGroupType($request)
    {
        try {
            $data = $this->groupTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertGroupTypeIndex($data, $this->params->groupTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->groupTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group_type'], $e);
        }
    }

    public function updateGroupType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->groupTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->groupTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertGroupTypeIndex($data, $this->params->groupTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->groupTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group_type'], $e);
        }
    }

    public function deleteGroupType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->groupTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->groupTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->groupTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->groupTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group_type'], $e);
        }
    }
}
