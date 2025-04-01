<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TestServiceReqListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TestServiceReqListVView\CreateTestServiceReqListVViewRequest;
use App\Http\Requests\TestServiceReqListVView\UpdateTestServiceReqListVViewRequest;
use App\Models\View\TestServiceReqListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TestServiceReqListVViewService;
use Illuminate\Http\Request;


class TestServiceReqListVViewController extends BaseApiCacheController
{
    protected $testServiceReqListVViewService;
    protected $testServiceReqListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TestServiceReqListVViewService $testServiceReqListVViewService, TestServiceReqListVView $testServiceReqListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->testServiceReqListVViewService = $testServiceReqListVViewService;
        $this->testServiceReqListVView = $testServiceReqListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->testServiceReqListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->testServiceReqListVViewDTO = new TestServiceReqListVViewDTO(
            $this->testServiceReqListVViewName,
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
            $this->lastId,
            $this->cursorPaginate,
            $this->treatmentCode,
            $this->patientCode,
            $this->status,
            $this->patientPhone,
            $this->param,
            $this->noCache,
        );
        $this->testServiceReqListVViewService->withParams($this->testServiceReqListVViewDTO);
    }
    public function index()
    {
        // Kiểm tra khoảng cách ngày
        if (($this->fromTime !== null) && ($this->toTime !== null)) {
            if (($this->toTime - $this->fromTime) > 60235959) {
                $this->errors[$this->fromTimeName] = 'Khoảng thời gian vượt quá 60 ngày!';
                $this->fromTime = null;
            }
        }
        if (($this->fromTime == null) && ($this->toTime == null) && (!$this->cursorPaginate)) {
            $this->errors[$this->fromTimeName] = 'Thiếu thời gian!';
            $this->errors[$this->toTimeName] = 'Thiếu thời gian!';
        }

        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->testServiceReqListVViewName);
            } else {
                $data = $this->testServiceReqListVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->testServiceReqListVViewName);
            } else {
                $data = $this->testServiceReqListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->testServiceReqListVView, $this->testServiceReqListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->testServiceReqListVViewName, $id);
        } else {
            $data = $this->testServiceReqListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }

    public function viewNoLogin(){

        if ($this->checkParam()) {
            return $this->checkParam();
        }

        $data = $this->testServiceReqListVViewService->handleViewNoLogin();
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
}
