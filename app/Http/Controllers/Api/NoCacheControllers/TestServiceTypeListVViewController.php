<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TestServiceTypeListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\TestServiceTypeListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestServiceTypeListVViewService;
use Illuminate\Http\Request;


class TestServiceTypeListVViewController extends BaseApiCacheController
{
    protected $testServiceTypeListVViewService;
    protected $testServiceTypeListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestServiceTypeListVViewService $testServiceTypeListVViewService, TestServiceTypeListVView $testServiceTypeListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testServiceTypeListVViewService = $testServiceTypeListVViewService;
        $this->testServiceTypeListVView = $testServiceTypeListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->testServiceTypeListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testServiceTypeListVViewDTO = new TestServiceTypeListVViewDTO(
            $this->testServiceTypeListVViewName,
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
            $this->treatmentId,
            $this->param,
        );
        $this->testServiceTypeListVViewService->withParams($this->testServiceTypeListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testServiceTypeListVViewName);
            } else {
                $data = $this->testServiceTypeListVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testServiceTypeListVViewName);
            } else {
                $data = $this->testServiceTypeListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testServiceTypeListVView, $this->testServiceTypeListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testServiceTypeListVViewName, $id);
        } else {
            $data = $this->testServiceTypeListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
