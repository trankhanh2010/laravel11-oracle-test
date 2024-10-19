<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentLViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentLView\CreateTreatmentLViewRequest;
use App\Http\Requests\TreatmentLView\UpdateTreatmentLViewRequest;
use App\Models\View\TreatmentLView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentLViewService;
use Illuminate\Http\Request;


class TreatmentLViewController extends BaseApiCacheController
{
    protected $treatmentLViewService;
    protected $treatmentLViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentLViewService $treatmentLViewService, TreatmentLView $treatmentLView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentLViewService = $treatmentLViewService;
        $this->treatmentLView = $treatmentLView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentLView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentLViewDTO = new TreatmentLViewDTO(
            $this->treatmentLViewName,
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
            $this->patientCode,
        );
        $this->treatmentLViewService->withParams($this->treatmentLViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentLViewName);
            } else {
                $data = $this->treatmentLViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->treatmentLViewName);
            } else {
                $data = $this->treatmentLViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentLView, $this->treatmentLViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentLViewName, $id);
        } else {
            $data = $this->treatmentLViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
