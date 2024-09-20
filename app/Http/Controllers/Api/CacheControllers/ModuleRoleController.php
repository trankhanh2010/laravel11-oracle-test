<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ModuleRoleDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ModuleRole\CreateModuleRoleRequest;
use App\Http\Requests\ModuleRole\UpdateModuleRoleRequest;
use App\Models\ACS\ModuleRole;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ModuleRoleService;
use Illuminate\Http\Request;


class ModuleRoleController extends BaseApiCacheController
{
    protected $moduleRoleService;
    protected $moduleRoleDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ModuleRoleService $moduleRoleService, ModuleRole $moduleRole)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->moduleRoleService = $moduleRoleService;
        $this->moduleRole = $moduleRole;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->moduleRole);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->moduleRoleDTO = new ModuleRoleDTO(
            $this->moduleRoleName,
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
            $this->moduleId,
            $this->roleId
        );
        $this->moduleRoleService->withParams($this->moduleRoleDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->moduleRoleName);
            } else {
                $data = $this->moduleRoleService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->moduleRoleName);
            } else {
                $data = $this->moduleRoleService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->moduleRole, $this->moduleRoleName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->moduleRoleName, $id);
        } else {
            $data = $this->moduleRoleService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateModuleRoleRequest $request)
    {
        return $this->moduleRoleService->createModuleRole($request);
    }
}
