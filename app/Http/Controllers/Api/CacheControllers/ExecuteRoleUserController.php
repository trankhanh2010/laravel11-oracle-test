<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExecuteRoleUserDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExecuteRoleUser\CreateExecuteRoleUserRequest;
use App\Http\Requests\ExecuteRoleUser\UpdateExecuteRoleUserRequest;
use App\Models\HIS\ExecuteRoleUser;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExecuteRoleUserService;
use Illuminate\Http\Request;


class ExecuteRoleUserController extends BaseApiCacheController
{
    protected $executeRoleUserService;
    protected $executeRoleUserDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExecuteRoleUserService $executeRoleUserService, ExecuteRoleUser $executeRoleUser)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->executeRoleUserService = $executeRoleUserService;
        $this->executeRoleUser = $executeRoleUser;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'tdl_username',
                'diploma',
                'tdl_email',
                'tdl_mobile',
                'DOB',
                'department_code',
                'department_name',
                'execute_role_code',
                'execute_role_name'
            ];
            $columns = $this->getColumnsTable($this->executeRoleUser);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->executeRoleUserDTO = new ExecuteRoleUserDTO(
            $this->executeRoleUserName,
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
            $this->loginname,
            $this->executeRoleId,
            $this->param,
            $this->noCache,
        );
        $this->executeRoleUserService->withParams($this->executeRoleUserDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->executeRoleUserName);
            } else {
                $data = $this->executeRoleUserService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->executeRoleUserName);
            } else {
                $data = $this->executeRoleUserService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->executeRoleUser, $this->executeRoleUserName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->executeRoleUserName, $id);
        } else {
            $data = $this->executeRoleUserService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExecuteRoleUserRequest $request)
    {
        return $this->executeRoleUserService->createExecuteRoleUser($request);
    }
}
