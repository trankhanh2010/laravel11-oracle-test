<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentListVView\CreateTreatmentListVViewRequest;
use App\Http\Requests\TreatmentListVView\UpdateTreatmentListVViewRequest;
use App\Models\View\TreatmentListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentListVViewService;
use Illuminate\Http\Request;


class TreatmentListVViewController extends BaseApiCacheController
{
    protected $treatmentListVViewService;
    protected $treatmentListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentListVViewService $treatmentListVViewService, TreatmentListVView $treatmentListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentListVViewService = $treatmentListVViewService;
        $this->treatmentListVView = $treatmentListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentListVViewDTO = new TreatmentListVViewDTO(
            $this->treatmentListVViewName,
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
            $this->groupBy,
            $this->patientCode,
            $this->param,
            $this->treatmentTypeCode,
            $this->inTimeFrom,
            $this->inTimeTo,

        );
        $this->treatmentListVViewService->withParams($this->treatmentListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->treatmentListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentListVView, $this->treatmentListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentListVViewName, $id);
        } else {
            $data = $this->treatmentListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
