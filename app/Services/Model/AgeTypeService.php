<?php

namespace App\Services\Model;

use App\DTOs\AgeTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AgeType\InsertAgeTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AgeTypeRepository;
use Illuminate\Support\Facades\Redis;

class AgeTypeService
{
    protected $ageTypeRepository;
    protected $params;
    public function __construct(AgeTypeRepository $ageTypeRepository)
    {
        $this->ageTypeRepository = $ageTypeRepository;
    }
    public function withParams(AgeTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->ageTypeRepository->applyJoins();
            $data = $this->ageTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ageTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ageTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ageTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->ageTypeRepository->applyJoins();
        $data = $this->ageTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->ageTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->ageTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->ageTypeRepository->applyJoins()
            ->where('his_age_type.id', $id);
        $data = $this->ageTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->ageTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ageTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->ageTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->ageTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }

    public function createAgeType($request)
    {
        try {
            $data = $this->ageTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAgeTypeIndex($data, $this->params->ageTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ageTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }

    public function updateAgeType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ageTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ageTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertAgeTypeIndex($data, $this->params->ageTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ageTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }

    public function deleteAgeType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ageTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ageTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ageTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ageTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['age_type'], $e);
        }
    }
}
