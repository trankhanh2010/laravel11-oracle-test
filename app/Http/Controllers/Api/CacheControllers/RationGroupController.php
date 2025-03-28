<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RationGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\RationGroup\CreateRationGroupRequest;
use App\Http\Requests\RationGroup\UpdateRationGroupRequest;
use App\Models\HIS\RationGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\RationGroupService;
use Illuminate\Http\Request;


class RationGroupController extends BaseApiCacheController
{
    protected $rationGroupService;
    protected $rationGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, RationGroupService $rationGroupService, RationGroup $rationGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->rationGroupService = $rationGroupService;
        $this->rationGroup = $rationGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->rationGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->rationGroupDTO = new RationGroupDTO(
            $this->rationGroupName,
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
        $this->rationGroupService->withParams($this->rationGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->rationGroupName);
            } else {
                $data = $this->rationGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->rationGroupName);
            } else {
                $data = $this->rationGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->rationGroup, $this->rationGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->rationGroupName, $id);
        } else {
            $data = $this->rationGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateRationGroupRequest $request)
    {
        return $this->rationGroupService->createRationGroup($request);
    }
    public function update(UpdateRationGroupRequest $request, $id)
    {
        return $this->rationGroupService->updateRationGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->rationGroupService->deleteRationGroup($id);
    }
}
