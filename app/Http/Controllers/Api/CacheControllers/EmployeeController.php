<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\EmployeeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Employee\CreateEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Requests\InfoUser\UpdateInfoUserRequest;
use App\Models\ACS\Token;
use App\Models\HIS\Employee;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\EmployeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as FacadesRequest;

class EmployeeController extends BaseApiCacheController
{
    protected $employeeService;
    protected $employeeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, EmployeeService $employeeService, Employee $employee, Token $token)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->employeeService = $employeeService;
        $this->employee = $employee;
        $this->token = $token;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_name',
                'department_code',
                'gender_name',
                'gender_code',
                'career_title_name',
                'career_title_code',
            ];
            $columns = $this->getColumnsTable($this->employee);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->employeeDTO = new EmployeeDTO(
            $this->employeeName,
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
        );
        $this->employeeService->withParams($this->employeeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->employeeName);
            } else {
                $data = $this->employeeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->employeeName);
            } else {
                $data = $this->employeeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->employee, $this->employeeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->employeeName, $id);
        } else {
            $data = $this->employeeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function infoUser(Request $request)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $login_name = $this->token->where('token_code', $request->bearerToken())->first()->login_name;
        $id = $this->employee->where('loginname', $login_name)->first()->id;
        if ($id !== null) {
            $validationError = $this->validateAndCheckId($id, $this->employee, $this->employeeName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->employeeService->handleDataBaseGetInfoUser($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateEmployeeRequest $request)
    {
        return $this->employeeService->createEmployee($request);
    }
    public function update(UpdateEmployeeRequest $request, $id)
    {
        return $this->employeeService->updateEmployee($id, $request);
    }
    public function updateInfoUser(UpdateInfoUserRequest $request)
    {
        $login_name = $this->token->where('token_code', $request->bearerToken())->first()->login_name;
        $id = $this->employee->where('loginname', $login_name)->first()->id;
        return $this->employeeService->updateInfoUser($id, $request);
    }
    public function destroy($id)
    {
        return $this->employeeService->deleteEmployee($id);
    }
}
