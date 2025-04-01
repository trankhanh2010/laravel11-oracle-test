<?php

namespace App\Services\Model;

use App\DTOs\PackageDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Package\InsertPackageIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PackageRepository;
use Illuminate\Support\Facades\Redis;

class PackageService
{
    protected $packageRepository;
    protected $params;
    public function __construct(PackageRepository $packageRepository)
    {
        $this->packageRepository = $packageRepository;
    }
    public function withParams(PackageDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->packageRepository->applyJoins();
            $data = $this->packageRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->packageRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->packageRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->packageRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->packageRepository->applyJoins();
        $data = $this->packageRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->packageRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->packageRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->packageRepository->applyJoins()
            ->where('his_package.id', $id);
        $data = $this->packageRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->packageName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->packageName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->packageName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->packageName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
    public function createPackage($request)
    {
        try {
            $data = $this->packageRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPackageIndex($data, $this->params->packageName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packageName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }

    public function updatePackage($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packageRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packageRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPackageIndex($data, $this->params->packageName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packageName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }

    public function deletePackage($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packageRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packageRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->packageName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packageName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['package'], $e);
        }
    }
}
