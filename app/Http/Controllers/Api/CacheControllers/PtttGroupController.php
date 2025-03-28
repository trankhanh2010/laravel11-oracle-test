<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PtttGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttGroup\CreatePtttGroupRequest;
use App\Http\Requests\PtttGroup\UpdatePtttGroupRequest;
use App\Models\HIS\PtttGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PtttGroupService;
use Illuminate\Http\Request;


class PtttGroupController extends BaseApiCacheController
{
    protected $ptttGroupService;
    protected $ptttGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PtttGroupService $ptttGroupService, PtttGroup $ptttGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ptttGroupService = $ptttGroupService;
        $this->ptttGroup = $ptttGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->ptttGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ptttGroupDTO = new PtttGroupDTO(
            $this->ptttGroupName,
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
        $this->ptttGroupService->withParams($this->ptttGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttGroupName);
            } else {
                $data = $this->ptttGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->ptttGroupName);
            } else {
                $data = $this->ptttGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ptttGroup, $this->ptttGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ptttGroupName, $id);
        } else {
            $data = $this->ptttGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePtttGroupRequest $request)
    {
        return $this->ptttGroupService->createPtttGroup($request);
    }
    public function update(UpdatePtttGroupRequest $request, $id)
    {
        return $this->ptttGroupService->updatePtttGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->ptttGroupService->deletePtttGroup($id);
    }
}
