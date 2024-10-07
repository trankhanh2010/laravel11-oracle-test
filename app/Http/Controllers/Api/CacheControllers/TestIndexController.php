<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TestIndexDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestIndex\CreateTestIndexRequest;
use App\Http\Requests\TestIndex\UpdateTestIndexRequest;
use App\Models\HIS\TestIndex;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestIndexService;
use Illuminate\Http\Request;


class TestIndexController extends BaseApiCacheController
{
    protected $testIndexService;
    protected $testIndexDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestIndexService $testIndexService, TestIndex $testIndex)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testIndexService = $testIndexService;
        $this->testIndex = $testIndex;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'service_code',
                'service_name',
                'test_index_unit_code',
                'test_index_unit_name',
                'test_index_group_code',
                'test_index_group_name',
                'material_type_code',
                'material_type_name',
                'test_service_type_code',
                'test_service_type_name',
            ];
            $columns = $this->getColumnsTable($this->testIndex);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testIndexDTO = new TestIndexDTO(
            $this->testIndexName,
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
        $this->testIndexService->withParams($this->testIndexDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testIndexName);
            } else {
                $data = $this->testIndexService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testIndexName);
            } else {
                $data = $this->testIndexService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testIndex, $this->testIndexName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testIndexName, $id);
        } else {
            $data = $this->testIndexService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }    public function store(CreateTestIndexRequest $request)
    {
        return $this->testIndexService->createTestIndex($request);
    }
    public function update(UpdateTestIndexRequest $request, $id)
    {
        return $this->testIndexService->updateTestIndex($id, $request);
    }
    public function destroy($id)
    {
        return $this->testIndexService->deleteTestIndex($id);
    }
}
