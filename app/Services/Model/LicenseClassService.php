<?php

namespace App\Services\Model;

use App\DTOs\LicenseClassDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\LicenseClass\InsertLicenseClassIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\LicenseClassRepository;
use Illuminate\Support\Facades\Redis;

class LicenseClassService
{
    protected $licenseClassRepository;
    protected $params;
    public function __construct(LicenseClassRepository $licenseClassRepository)
    {
        $this->licenseClassRepository = $licenseClassRepository;
    }
    public function withParams(LicenseClassDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->licenseClassRepository->applyJoins();
            $data = $this->licenseClassRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->licenseClassRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->licenseClassRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->licenseClassRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->licenseClassRepository->applyJoins();
        $data = $this->licenseClassRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->licenseClassRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->licenseClassRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->licenseClassRepository->applyJoins()
            ->where('his_license_class.id', $id);
        $data = $this->licenseClassRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->licenseClassName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->licenseClassName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->licenseClassName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->licenseClassName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }

    public function createLicenseClass($request)
    {
        try {
            $data = $this->licenseClassRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertLicenseClassIndex($data, $this->params->licenseClassName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->licenseClassName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }

    public function updateLicenseClass($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->licenseClassRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->licenseClassRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertLicenseClassIndex($data, $this->params->licenseClassName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->licenseClassName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }

    public function deleteLicenseClass($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->licenseClassRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->licenseClassRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->licenseClassName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->licenseClassName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['license_class'], $e);
        }
    }
}
