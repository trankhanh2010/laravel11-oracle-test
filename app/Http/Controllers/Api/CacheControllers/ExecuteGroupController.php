<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExecuteGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExecuteGroup\CreateExecuteGroupRequest;
use App\Http\Requests\ExecuteGroup\UpdateExecuteGroupRequest;
use App\Models\HIS\ExecuteGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExecuteGroupService;
use Illuminate\Http\Request;


class ExecuteGroupController extends BaseApiCacheController
{
    protected $executeGroupService;
    protected $executeGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExecuteGroupService $executeGroupService, ExecuteGroup $executeGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->executeGroupService = $executeGroupService;
        $this->executeGroup = $executeGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->executeGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->executeGroupDTO = new ExecuteGroupDTO(
            $this->executeGroupName,
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
        $this->executeGroupService->withParams($this->executeGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->executeGroupName);
            } else {
                $data = $this->executeGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->executeGroupName);
            } else {
                $data = $this->executeGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->executeGroup, $this->executeGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->executeGroupName, $id);
        } else {
            $data = $this->executeGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExecuteGroupRequest $request)
    {
        return $this->executeGroupService->createExecuteGroup($request);
    }
    public function update(UpdateExecuteGroupRequest $request, $id)
    {
        return $this->executeGroupService->updateExecuteGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->executeGroupService->deleteExecuteGroup($id);
    }
}
