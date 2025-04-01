<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentFeeViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentFeeView\CreateTreatmentFeeViewRequest;
use App\Http\Requests\TreatmentFeeView\UpdateTreatmentFeeViewRequest;
use App\Models\View\TreatmentFeeView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentFeeViewService;
use Illuminate\Http\Request;


class TreatmentFeeViewController extends BaseApiCacheController
{
    protected $treatmentFeeViewService;
    protected $treatmentFeeViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentFeeViewService $treatmentFeeViewService, TreatmentFeeView $treatmentFeeView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentFeeViewService = $treatmentFeeViewService;
        $this->treatmentFeeView = $treatmentFeeView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentFeeView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentFeeViewDTO = new TreatmentFeeViewDTO(
            $this->treatmentFeeViewName,
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
            $this->tdlTreatmentTypeIds,
            $this->tdlPatientTypeIds,
            $this->branchId,
            $this->inDateFrom,
            $this->inDateTo,
            $this->isApproveStore,
            $this->param,
            $this->noCache,
        );
        $this->treatmentFeeViewService->withParams($this->treatmentFeeViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentFeeViewName);
            } else {
                $data = $this->treatmentFeeViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->treatmentFeeViewName);
            } else {
                $data = $this->treatmentFeeViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentFeeView, $this->treatmentFeeViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentFeeViewName, $id);
        } else {
            $data = $this->treatmentFeeViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
