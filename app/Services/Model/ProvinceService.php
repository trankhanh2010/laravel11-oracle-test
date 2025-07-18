<?php

namespace App\Services\Model;

use App\DTOs\ProvinceDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Province\InsertProvinceIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ProvinceRepository;
use Illuminate\Support\Facades\Redis;

class ProvinceService
{
    protected $provinceRepository;
    protected $params;
    public function __construct(ProvinceRepository $provinceRepository)
    {
        $this->provinceRepository = $provinceRepository;
    }
    public function withParams(ProvinceDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->provinceRepository->applyJoins();
            $data = $this->provinceRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->provinceRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->provinceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->provinceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->provinceRepository->applyJoins();
        $data = $this->provinceRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->provinceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->provinceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseGetDataSelect()
    {
        $data = $this->provinceRepository->applyJoinsGetDataSelect();
        $data = $this->provinceRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->provinceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->provinceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseGetDataSelect2Cap()
    {
        $data = $this->provinceRepository->applyJoinsGetDataSelect();
        $data = $this->provinceRepository->applyIsActiveFilter($data, 1);
        $data = $this->provinceRepository->applyIsDeleteFilter($data, 0);
        $data = $this->provinceRepository->apply2CapFilter($data);
        $count = $data->count();
        $data = $this->provinceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->provinceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->provinceRepository->applyJoins()
            ->where('sda_province.id', $id);
        $data = $this->provinceRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->provinceName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->provinceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }
    public function handleDataBaseGetAllGetDataSelect()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabaseGetDataSelect();
            } else {
                $cacheKey = $this->params->provinceName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->provinceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabaseGetDataSelect();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }
    public function handleDataBaseGetAllGetDataSelect2Cap()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabaseGetDataSelect2Cap();
            } else {
                $cacheKey = $this->params->provinceName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->provinceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabaseGetDataSelect2Cap();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->provinceName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->provinceName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }

    public function createProvince($request)
    {
        try {
            $data = $this->provinceRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertProvinceIndex($data, $this->params->provinceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->provinceName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }

    public function updateProvince($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->provinceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->provinceRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertProvinceIndex($data, $this->params->provinceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->provinceName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }

    public function deleteProvince($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->provinceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->provinceRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->provinceName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->provinceName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['province'], $e);
        }
    }
}
