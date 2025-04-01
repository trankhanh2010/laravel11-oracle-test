<?php

namespace App\Services\Model;

use App\DTOs\ExeServiceModuleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExeServiceModule\InsertExeServiceModuleIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExeServiceModuleRepository;
use Illuminate\Support\Facades\Redis;

class ExeServiceModuleService
{
    protected $exeServiceModuleRepository;
    protected $params;
    public function __construct(ExeServiceModuleRepository $exeServiceModuleRepository)
    {
        $this->exeServiceModuleRepository = $exeServiceModuleRepository;
    }
    public function withParams(ExeServiceModuleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->exeServiceModuleRepository->applyJoins();
            $data = $this->exeServiceModuleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->exeServiceModuleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->exeServiceModuleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->exeServiceModuleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->exeServiceModuleRepository->applyJoins();
        $data = $this->exeServiceModuleRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->exeServiceModuleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->exeServiceModuleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->exeServiceModuleRepository->applyJoins()
            ->where('his_exe_service_module.id', $id);
        $data = $this->exeServiceModuleRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->exeServiceModuleName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->exeServiceModuleName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->exeServiceModuleName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->exeServiceModuleName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
    public function createExeServiceModule($request)
    {
        try {
            $data = $this->exeServiceModuleRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExeServiceModuleIndex($data, $this->params->exeServiceModuleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->exeServiceModuleName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }

    public function updateExeServiceModule($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->exeServiceModuleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->exeServiceModuleRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertExeServiceModuleIndex($data, $this->params->exeServiceModuleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->exeServiceModuleName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }

    public function deleteExeServiceModule($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->exeServiceModuleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->exeServiceModuleRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->exeServiceModuleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->exeServiceModuleName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exe_service_module'], $e);
        }
    }
}
