<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TestSampleTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestSampleType\CreateTestSampleTypeRequest;
use App\Http\Requests\TestSampleType\UpdateTestSampleTypeRequest;
use App\Models\HIS\TestSampleType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestSampleTypeService;
use Illuminate\Http\Request;


class TestSampleTypeController extends BaseApiCacheController
{
    protected $testSampleTypeService;
    protected $testSampleTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestSampleTypeService $testSampleTypeService, TestSampleType $testSampleType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testSampleTypeService = $testSampleTypeService;
        $this->testSampleType = $testSampleType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->testSampleType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testSampleTypeDTO = new TestSampleTypeDTO(
            $this->testSampleTypeName,
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
        $this->testSampleTypeService->withParams($this->testSampleTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testSampleTypeName);
            } else {
                $data = $this->testSampleTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testSampleTypeName);
            } else {
                $data = $this->testSampleTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testSampleType, $this->testSampleTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testSampleTypeName, $id);
        } else {
            $data = $this->testSampleTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTestSampleTypeRequest $request)
    {
        return $this->testSampleTypeService->createTestSampleType($request);
    }
    public function update(UpdateTestSampleTypeRequest $request, $id)
    {
        return $this->testSampleTypeService->updateTestSampleType($id, $request);
    }
    public function destroy($id)
    {
        return $this->testSampleTypeService->deleteTestSampleType($id);
    }
}
