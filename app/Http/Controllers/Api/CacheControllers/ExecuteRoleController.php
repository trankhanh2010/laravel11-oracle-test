<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExecuteRoleDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExecuteRole\CreateExecuteRoleRequest;
use App\Http\Requests\ExecuteRole\UpdateExecuteRoleRequest;
use App\Models\HIS\ExecuteRole;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExecuteRoleService;
use Illuminate\Http\Request;


class ExecuteRoleController extends BaseApiCacheController
{
    protected $executeRoleService;
    protected $executeRoleDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExecuteRoleService $executeRoleService, ExecuteRole $executeRole)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->executeRoleService = $executeRoleService;
        $this->executeRole = $executeRole;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->executeRole);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->executeRoleDTO = new ExecuteRoleDTO(
            $this->executeRoleName,
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
        $this->executeRoleService->withParams($this->executeRoleDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->executeRoleName);
            } else {
                $data = $this->executeRoleService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->executeRoleName);
            } else {
                $data = $this->executeRoleService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->executeRole, $this->executeRoleName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->executeRoleName, $id);
        } else {
            $data = $this->executeRoleService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExecuteRoleRequest $request)
    {
        return $this->executeRoleService->createExecuteRole($request);
    }
    public function update(UpdateExecuteRoleRequest $request, $id)
    {
        return $this->executeRoleService->updateExecuteRole($id, $request);
    }
    public function destroy($id)
    {
        return $this->executeRoleService->deleteExecuteRole($id);
    }
}
