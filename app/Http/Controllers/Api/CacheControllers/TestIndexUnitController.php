<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TestIndexUnitDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestIndexUnit\CreateTestIndexUnitRequest;
use App\Http\Requests\TestIndexUnit\UpdateTestIndexUnitRequest;
use App\Models\HIS\TestIndexUnit;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestIndexUnitService;
use Illuminate\Http\Request;


class TestIndexUnitController extends BaseApiCacheController
{
    protected $testIndexUnitService;
    protected $testIndexUnitDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestIndexUnitService $testIndexUnitService, TestIndexUnit $testIndexUnit)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testIndexUnitService = $testIndexUnitService;
        $this->testIndexUnit = $testIndexUnit;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->testIndexUnit);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testIndexUnitDTO = new TestIndexUnitDTO(
            $this->testIndexUnitName,
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
        $this->testIndexUnitService->withParams($this->testIndexUnitDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testIndexUnitName);
            } else {
                $data = $this->testIndexUnitService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testIndexUnitName);
            } else {
                $data = $this->testIndexUnitService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testIndexUnit, $this->testIndexUnitName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testIndexUnitName, $id);
        } else {
            $data = $this->testIndexUnitService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTestIndexUnitRequest $request)
    {
        return $this->testIndexUnitService->createTestIndexUnit($request);
    }
    public function update(UpdateTestIndexUnitRequest $request, $id)
    {
        return $this->testIndexUnitService->updateTestIndexUnit($id, $request);
    }
    public function destroy($id)
    {
        return $this->testIndexUnitService->deleteTestIndexUnit($id);
    }
}
