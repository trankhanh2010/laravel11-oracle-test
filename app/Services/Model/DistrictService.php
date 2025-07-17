<?php

namespace App\Services\Model;

use App\DTOs\DistrictDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\District\InsertDistrictIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DistrictRepository;
use Illuminate\Support\Facades\Redis;

class DistrictService
{
    protected $districtRepository;
    protected $params;
    public function __construct(DistrictRepository $districtRepository)
    {
        $this->districtRepository = $districtRepository;
    }
    public function withParams(DistrictDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->districtRepository->applyJoins();
            $data = $this->districtRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->districtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->districtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->districtRepository->applyJoins();
        $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->districtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->districtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseGetDataSelect()
    {
        $data = $this->districtRepository->applyJoinsGetDataSelect();
        $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->districtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->districtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->districtRepository->applyJoins()
            ->where('sda_district.id', $id);
        $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->districtName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->districtName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
    public function handleDataBaseGetAllGetDataSelect()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabaseGetDataSelect();
            } else {
                $cacheKey = $this->params->districtName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->districtName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabaseGetDataSelect();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->districtName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->districtName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }

    public function createDistrict($request)
    {
        try {
            $data = $this->districtRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDistrictIndex($data, $this->params->districtName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->districtName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }

    public function updateDistrict($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->districtRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->districtRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertDistrictIndex($data, $this->params->districtName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->districtName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }

    public function deleteDistrict($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->districtRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->districtRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->districtName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->districtName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
}
