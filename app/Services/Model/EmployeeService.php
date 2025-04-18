<?php

namespace App\Services\Model;

use App\DTOs\EmployeeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Employee\InsertEmployeeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EmployeeRepository;
use Illuminate\Support\Facades\Redis;

class EmployeeService
{
    protected $employeeRepository;
    protected $params;
    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }
    public function withParams(EmployeeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->employeeRepository->applyJoins();
            $data = $this->employeeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->employeeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->employeeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->employeeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->employeeRepository->applyJoins();
        $data = $this->employeeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->employeeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->employeeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->employeeRepository->applyJoins()
            ->where('his_employee.id', $id);
        $data = $this->employeeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->employeeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->employeeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->employeeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->employeeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }
    public function handleDataBaseGetInfoUser($id)
    {
        try {
            $data = Cache::remember($this->params->employeeName . '_info_user_' . $id, $this->params->time, function () use ($id) {
                $data = $this->employeeRepository->getInfoUser($id);
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }
    public function createEmployee($request)
    {
        try {
            $data = $this->employeeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmployeeIndex($data, $this->params->employeeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->employeeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }

    public function updateEmployee($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->employeeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->employeeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmployeeIndex($data, $this->params->employeeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->employeeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }

    public function updateInfoUser($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->employeeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->employeeRepository->updateInfoUser($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmployeeIndex($data, $this->params->employeeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->employeeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }

    public function deleteEmployee($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->employeeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->employeeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->employeeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->employeeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['employee'], $e);
        }
    }
}
