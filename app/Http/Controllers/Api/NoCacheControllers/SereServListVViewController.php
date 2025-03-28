<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServListVView\CreateSereServListVViewRequest;
use App\Http\Requests\SereServListVView\UpdateSereServListVViewRequest;
use App\Models\View\SereServListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServListVViewService;
use Illuminate\Http\Request;


class SereServListVViewController extends BaseApiCacheController
{
    protected $sereServListVViewService;
    protected $sereServListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServListVViewService $sereServListVViewService, SereServListVView $sereServListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServListVViewService = $sereServListVViewService;
        $this->sereServListVView = $sereServListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServListVViewDTO = new SereServListVViewDTO(
            $this->sereServListVViewName,
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
            $this->param,
        );
        $this->sereServListVViewService->withParams($this->sereServListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->sereServListVViewName);
            } else {
                $data = $this->sereServListVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->sereServListVViewName);
            } else {
                $data = $this->sereServListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServListVView, $this->sereServListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServListVViewName, $id);
        } else {
            $data = $this->sereServListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
