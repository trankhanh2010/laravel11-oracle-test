<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\GroupTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\GroupType\CreateGroupTypeRequest;
use App\Http\Requests\GroupType\UpdateGroupTypeRequest;
use App\Models\SDA\GroupType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\GroupTypeService;
use Illuminate\Http\Request;


class GroupTypeController extends BaseApiCacheController
{
    protected $groupTypeService;
    protected $groupTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, GroupTypeService $groupTypeService, GroupType $groupType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->groupTypeService = $groupTypeService;
        $this->groupType = $groupType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->groupType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->groupTypeDTO = new GroupTypeDTO(
            $this->groupTypeName,
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
        $this->groupTypeService->withParams($this->groupTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->groupTypeName);
            } else {
                $data = $this->groupTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->groupTypeName);
            } else {
                $data = $this->groupTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->groupType, $this->groupTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->groupTypeName, $id);
        } else {
            $data = $this->groupTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateGroupTypeRequest $request)
    {
        return $this->groupTypeService->createGroupType($request);
    }
    public function update(UpdateGroupTypeRequest $request, $id)
    {
        return $this->groupTypeService->updateGroupType($id, $request);
    }
    public function destroy($id)
    {
        return $this->groupTypeService->deleteGroupType($id);
    }
}
