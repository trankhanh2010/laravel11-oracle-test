<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DepartmentDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Models\HIS\Department;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DepartmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DepartmentController extends BaseApiCacheController
{
    protected $departmentService;
    protected $departmentDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DepartmentService $departmentService, Department $department)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->departmentService = $departmentService;
        $this->department = $department;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'branch_code',
                'branch_name',
                'patient_type_code',
                'patient_type_name',
                'treatment_type_code',
                'treatment_type_name',
            ];
            $columns = $this->getColumnsTable($this->department);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->departmentDTO = new DepartmentDTO(
            $this->departmentName,
            $this->keyword,
            $this->isActive,
            $this->orderBy,
            $this->orderByJoin,
            $this->orderByString,
            $this->getAll,
            $this->start,
            $this->limit,
            $request,
            $this->appCreator, 
            $this->appModifier, 
            $this->time,
            $this->tab,
        );
        $this->departmentService->withParams($this->departmentDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $source = [
            'id',
            'department_code',
            'department_name',
        ];
        $this->elasticCustom = $this->departmentService->handleCustomParamElasticSearch();
        if ($this->elasticSearchType || $this->elastic) {
            if(!$keyword){
                $data = Cache::remember($this->departmentName.'_' . $this->param, $this->time, function () use($source) {
                    $data = $this->elasticSearchService->handleElasticSearchSearch($this->departmentName, $this->elasticCustom, $source);
                    return $data;
                });
            }else{
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->departmentName, $this->elasticCustom, $source);
            }
        } else {
            if ($keyword) {
                $data = $this->departmentService->handleDataBaseSearch();
            } else {
                $data = $this->departmentService->handleDataBaseGetAll();
            }
        }
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->keywordName => $this->keyword,
            $this->orderByName => $this->orderByRequest
        ];
        return returnDataSuccess($paramReturn, $data['data']);
    }

    public function show($id)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($id !== null) {
            $validationError = $this->validateAndCheckId($id, $this->department, $this->departmentName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->departmentName, $id);
        } else {
            $data = $this->departmentService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDepartmentRequest $request)
    {
        return $this->departmentService->createDepartment($request);
    }
    public function update(UpdateDepartmentRequest $request, $id)
    {
        return $this->departmentService->updateDepartment($id, $request);
    }
    public function destroy($id)
    {
        return $this->departmentService->deleteDepartment($id);
    }
}
