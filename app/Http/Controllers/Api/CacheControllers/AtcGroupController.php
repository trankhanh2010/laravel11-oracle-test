<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AtcGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AtcGroup\CreateAtcGroupRequest;
use App\Http\Requests\AtcGroup\UpdateAtcGroupRequest;
use App\Models\HIS\AtcGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AtcGroupService;
use Illuminate\Http\Request;

class AtcGroupController extends BaseApiCacheController
{
    protected $atcGroupService;
    protected $atcGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AtcGroupService $atcGroupService, AtcGroup $atcGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->atcGroupService = $atcGroupService;
        $this->atcGroup = $atcGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->atcGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
            $this->orderByString = arrayToCustomString($this->orderBy);
        }
        // Thêm tham số vào service
        $this->atcGroupDTO = new AtcGroupDTO(
            $this->atcGroupName,
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
            $this->noCache,
        );
        $this->atcGroupService->withParams($this->atcGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->atcGroupName);
            } else {
                $data = $this->atcGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->atcGroupName);
            } else {
                $data = $this->atcGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->atcGroup, $this->atcGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->atcGroupName, $id);
        } else {
            $data = $this->atcGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAtcGroupRequest $request)
    {
        return $this->atcGroupService->createAtcGroup($request);
    }
    public function update(UpdateAtcGroupRequest $request, $id)
    {
        return $this->atcGroupService->updateAtcGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->atcGroupService->deleteAtcGroup($id);
    }
}
