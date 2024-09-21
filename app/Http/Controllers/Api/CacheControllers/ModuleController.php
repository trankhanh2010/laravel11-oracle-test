<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ModuleDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Module\CreateModuleRequest;
use App\Http\Requests\Module\UpdateModuleRequest;
use App\Models\ACS\Module;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ModuleService;
use Illuminate\Http\Request;


class ModuleController extends BaseApiCacheController
{
    protected $moduleService;
    protected $moduleDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ModuleService $moduleService, Module $module)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->moduleService = $moduleService;
        $this->module = $module;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'module_group_code',
                'module_group_name'
            ];
            $columns = $this->getColumnsTable($this->module);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->moduleDTO = new ModuleDTO(
            $this->moduleName,
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
        $this->moduleService->withParams($this->moduleDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->moduleName);
            } else {
                $data = $this->moduleService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->moduleName);
            } else {
                $data = $this->moduleService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->module, $this->moduleName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->moduleName, $id);
        } else {
            $data = $this->moduleService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateModuleRequest $request)
    {
        return $this->moduleService->createModule($request);
    }
    public function update(UpdateModuleRequest $request, $id)
    {
        return $this->moduleService->updateModule($id, $request);
    }
    public function destroy($id)
    {
        return $this->moduleService->deleteModule($id);
    }
}
