<?php

namespace App\Services\Model;

use App\DTOs\DepartmentDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Department\InsertDepartmentIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DepartmentRepository;

class DepartmentService 
{
    protected $departmentRepository;
    protected $params;
    public function __construct(DepartmentRepository $departmentRepository)
    {
        $this->departmentRepository = $departmentRepository;
    }
    public function withParams(DepartmentDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->departmentRepository->applyJoins();
            $data = $this->departmentRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->departmentRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->departmentRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->departmentRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['department'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->departmentName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->departmentRepository->applyJoins();
                $data = $this->departmentRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->departmentRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->departmentRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['department'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->departmentName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->departmentRepository->applyJoins()
                    ->where('his_department.id', $id);
                $data = $this->departmentRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['department'], $e);
        }
    }

    public function createDepartment($request)
    {
        try {
            $data = $this->departmentRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->departmentName));
            // Gọi event để thêm index vào elastic
            event(new InsertDepartmentIndex($data, $this->params->departmentName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['department'], $e);
        }
    }

    public function updateDepartment($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->departmentRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->departmentRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->departmentName));
            // Gọi event để thêm index vào elastic
            event(new InsertDepartmentIndex($data, $this->params->departmentName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['department'], $e);
        }
    }

    public function deleteDepartment($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->departmentRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->departmentRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->departmentName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->departmentName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['department'], $e);
        }
    }
}