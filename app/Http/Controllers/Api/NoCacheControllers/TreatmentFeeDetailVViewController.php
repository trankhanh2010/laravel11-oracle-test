<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentFeeDetailVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentFeeDetailVView\CreateTreatmentFeeDetailVViewRequest;
use App\Http\Requests\TreatmentFeeDetailVView\UpdateTreatmentFeeDetailVViewRequest;
use App\Models\View\TreatmentFeeDetailVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentFeeDetailVViewService;
use Illuminate\Http\Request;


class TreatmentFeeDetailVViewController extends BaseApiCacheController
{
    protected $treatmentFeeDetailVViewService;
    protected $treatmentFeeDetailVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentFeeDetailVViewService $treatmentFeeDetailVViewService, TreatmentFeeDetailVView $treatmentFeeDetailVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentFeeDetailVViewService = $treatmentFeeDetailVViewService;
        $this->treatmentFeeDetailVView = $treatmentFeeDetailVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentFeeDetailVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Kiểm tra tham số
        if (($this->treatmentId == null) ) {
            $this->errors[$this->treatmentIdName] = 'Thiếu Id điều trị!';
        }
        // Thêm tham số vào service
        $this->treatmentFeeDetailVViewDTO = new TreatmentFeeDetailVViewDTO(
            $this->treatmentFeeDetailVViewName,
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
            $this->treatmentCode,
        );
        $this->treatmentFeeDetailVViewService->withParams($this->treatmentFeeDetailVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->treatmentFeeDetailVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentFeeDetailVView, $this->treatmentFeeDetailVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentFeeDetailVViewName, $id);
        } else {
            $data = $this->treatmentFeeDetailVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
