<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TestServiceReqListVView2DTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestServiceReqListVView2\CreateTestServiceReqListVView2Request;
use App\Http\Requests\TestServiceReqListVView2\UpdateTestServiceReqListVView2Request;
use App\Models\View\TestServiceReqListVView2;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestServiceReqListVView2Service;
use Illuminate\Http\Request;


class TestServiceReqListVView2Controller extends BaseApiCacheController
{
    protected $testServiceReqListVView2Service;
    protected $testServiceReqListVView2DTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestServiceReqListVView2Service $testServiceReqListVView2Service, TestServiceReqListVView2 $testServiceReqListVView2)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testServiceReqListVView2Service = $testServiceReqListVView2Service;
        $this->testServiceReqListVView2 = $testServiceReqListVView2;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->testServiceReqListVView2, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testServiceReqListVView2DTO = new TestServiceReqListVView2DTO(
            $this->testServiceReqListVView2Name,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->fromTime,
            $this->toTime,
            $this->executeDepartmentCode,
            $this->isNoExcute,
            $this->isSpecimen,
            $this->param,
        );
        $this->testServiceReqListVView2Service->withParams($this->testServiceReqListVView2DTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testServiceReqListVView2Name);
            } else {
                $data = $this->testServiceReqListVView2Service->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testServiceReqListVView2Name);
            } else {
                $data = $this->testServiceReqListVView2Service->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testServiceReqListVView2, $this->testServiceReqListVView2Name);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testServiceReqListVView2Name, $id);
        } else {
            $data = $this->testServiceReqListVView2Service->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
