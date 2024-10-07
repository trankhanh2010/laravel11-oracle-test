<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TestIndexGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestIndexGroup\CreateTestIndexGroupRequest;
use App\Http\Requests\TestIndexGroup\UpdateTestIndexGroupRequest;
use App\Models\HIS\TestIndexGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestIndexGroupService;
use Illuminate\Http\Request;


class TestIndexGroupController extends BaseApiCacheController
{
    protected $testIndexGroupService;
    protected $testIndexGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestIndexGroupService $testIndexGroupService, TestIndexGroup $testIndexGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testIndexGroupService = $testIndexGroupService;
        $this->testIndexGroup = $testIndexGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->testIndexGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testIndexGroupDTO = new TestIndexGroupDTO(
            $this->testIndexGroupName,
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
        $this->testIndexGroupService->withParams($this->testIndexGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testIndexGroupName);
            } else {
                $data = $this->testIndexGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testIndexGroupName);
            } else {
                $data = $this->testIndexGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testIndexGroup, $this->testIndexGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testIndexGroupName, $id);
        } else {
            $data = $this->testIndexGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTestIndexGroupRequest $request)
    {
        return $this->testIndexGroupService->createTestIndexGroup($request);
    }
    public function update(UpdateTestIndexGroupRequest $request, $id)
    {
        return $this->testIndexGroupService->updateTestIndexGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->testIndexGroupService->deleteTestIndexGroup($id);
    }
}
