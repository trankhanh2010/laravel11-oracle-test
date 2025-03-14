<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServClsListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServClsListVView\CreateSereServClsListVViewRequest;
use App\Http\Requests\SereServClsListVView\UpdateSereServClsListVViewRequest;
use App\Models\View\SereServClsListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServClsListVViewService;
use Illuminate\Http\Request;


class SereServClsListVViewController extends BaseApiCacheController
{
    protected $sereServClsListVViewService;
    protected $sereServClsListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServClsListVViewService $sereServClsListVViewService, SereServClsListVView $sereServClsListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServClsListVViewService = $sereServClsListVViewService;
        $this->sereServClsListVView = $sereServClsListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServClsListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServClsListVViewDTO = new SereServClsListVViewDTO(
            $this->sereServClsListVViewName,
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
            $this->treatmentId,
            $this->trackingId,
            $this->serviceReqId,
            $this->groupBy,
            $this->notInTracking,
            $this->patientCode,
            $this->serviceTypeCodes,
            $this->serviceTypeCodesString,
            $this->groupByString,
            $this->tab,
            $this->param,
            $this->intructionTimeTo,
            $this->intructionTimeFrom,
            $this->reportTypeCode,
        );
        $this->sereServClsListVViewService->withParams($this->sereServClsListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->sereServClsListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServClsListVView, $this->sereServClsListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServClsListVViewName, $id);
        } else {
            $data = $this->sereServClsListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
