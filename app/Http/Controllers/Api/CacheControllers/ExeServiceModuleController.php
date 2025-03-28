<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExeServiceModuleDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExeServiceModule\CreateExeServiceModuleRequest;
use App\Http\Requests\ExeServiceModule\UpdateExeServiceModuleRequest;
use App\Models\HIS\ExeServiceModule;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExeServiceModuleService;
use Illuminate\Http\Request;


class ExeServiceModuleController extends BaseApiCacheController
{
    protected $exeServiceModuleService;
    protected $exeServiceModuleDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExeServiceModuleService $exeServiceModuleService, ExeServiceModule $exeServiceModule)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->exeServiceModuleService = $exeServiceModuleService;
        $this->exeServiceModule = $exeServiceModule;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->exeServiceModule);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->exeServiceModuleDTO = new ExeServiceModuleDTO(
            $this->exeServiceModuleName,
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
            $this->param,
        );
        $this->exeServiceModuleService->withParams($this->exeServiceModuleDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->exeServiceModuleName);
            } else {
                $data = $this->exeServiceModuleService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->exeServiceModuleName);
            } else {
                $data = $this->exeServiceModuleService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->exeServiceModule, $this->exeServiceModuleName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->exeServiceModuleName, $id);
        } else {
            $data = $this->exeServiceModuleService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExeServiceModuleRequest $request)
    {
        return $this->exeServiceModuleService->createExeServiceModule($request);
    }
    public function update(UpdateExeServiceModuleRequest $request, $id)
    {
        return $this->exeServiceModuleService->updateExeServiceModule($id, $request);
    }
    public function destroy($id)
    {
        return $this->exeServiceModuleService->deleteExeServiceModule($id);
    }
}
