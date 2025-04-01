<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServDepositVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SereServDepositVView\CreateSereServDepositVViewRequest;
use App\Http\Requests\SereServDepositVView\UpdateSereServDepositVViewRequest;
use App\Models\View\SereServDepositVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SereServDepositVViewService;
use Illuminate\Http\Request;


class SereServDepositVViewController extends BaseApiCacheController
{
    protected $sereServDepositVViewService;
    protected $sereServDepositVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, SereServDepositVViewService $sereServDepositVViewService, SereServDepositVView $sereServDepositVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->sereServDepositVViewService = $sereServDepositVViewService;
        $this->sereServDepositVView = $sereServDepositVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServDepositVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServDepositVViewDTO = new SereServDepositVViewDTO(
            $this->sereServDepositVViewName,
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
            $this->tdlTreatmentId,
            $this->param,
            $this->noCache,
        );
        $this->sereServDepositVViewService->withParams($this->sereServDepositVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->sereServDepositVViewName);
            } else {
                $data = $this->sereServDepositVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->sereServDepositVViewName);
            } else {
                $data = $this->sereServDepositVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServDepositVView, $this->sereServDepositVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->sereServDepositVViewName, $id);
        } else {
            $data = $this->sereServDepositVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
