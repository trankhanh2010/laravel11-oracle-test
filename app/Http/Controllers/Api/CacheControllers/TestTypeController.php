<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TestTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestType\CreateTestTypeRequest;
use App\Http\Requests\TestType\UpdateTestTypeRequest;
use App\Models\HIS\TestType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestTypeService;
use Illuminate\Http\Request;


class TestTypeController extends BaseApiCacheController
{
    protected $testTypeService;
    protected $testTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestTypeService $testTypeService, TestType $testType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testTypeService = $testTypeService;
        $this->testType = $testType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->testType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testTypeDTO = new TestTypeDTO(
            $this->testTypeName,
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
        $this->testTypeService->withParams($this->testTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testTypeName);
            } else {
                $data = $this->testTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testTypeName);
            } else {
                $data = $this->testTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testType, $this->testTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testTypeName, $id);
        } else {
            $data = $this->testTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTestTypeRequest $request)
    {
        return $this->testTypeService->createTestType($request);
    }
    public function update(UpdateTestTypeRequest $request, $id)
    {
        return $this->testTypeService->updateTestType($id, $request);
    }
    public function destroy($id)
    {
        return $this->testTypeService->deleteTestType($id);
    }
}
