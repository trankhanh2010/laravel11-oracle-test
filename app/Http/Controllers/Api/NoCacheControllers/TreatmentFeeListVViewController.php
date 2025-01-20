<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentFeeListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentFeeListVView\CreateTreatmentFeeListVViewRequest;
use App\Http\Requests\TreatmentFeeListVView\UpdateTreatmentFeeListVViewRequest;
use App\Models\View\TreatmentFeeListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentFeeListVViewService;
use Illuminate\Http\Request;


class TreatmentFeeListVViewController extends BaseApiCacheController
{
    protected $treatmentFeeListVViewService;
    protected $treatmentFeeListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentFeeListVViewService $treatmentFeeListVViewService, TreatmentFeeListVView $treatmentFeeListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentFeeListVViewService = $treatmentFeeListVViewService;
        $this->treatmentFeeListVView = $treatmentFeeListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->treatmentFeeListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        if(($this->treatmentCode != null || $this->patientCode != null)){
            $this->cursorPaginate = false;
            $this->getAll = true;
        }
        // Thêm tham số vào service
        $this->treatmentFeeListVViewDTO = new TreatmentFeeListVViewDTO(
            $this->treatmentFeeListVViewName,
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
            $this->lastId,
            $this->cursorPaginate,
            $this->treatmentCode,
            $this->patientCode,
            $this->status,
            $this->patientPhone,
        );
        $this->treatmentFeeListVViewService->withParams($this->treatmentFeeListVViewDTO);
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
        if($this->treatmentCode == null && $this->patientCode == null){
            if (($this->fromTime == null) && ($this->toTime == null) && (!$this->cursorPaginate)) {
                $this->errors[$this->fromTimeName] = 'Thiếu thời gian!';
                $this->errors[$this->toTimeName] = 'Thiếu thời gian!';
            }
        }

        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        $data = $this->treatmentFeeListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentFeeListVView, $this->treatmentFeeListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }

        $data = $this->treatmentFeeListVViewService->handleDataBaseGetWithId($id);
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

        $data = $this->treatmentFeeListVViewService->handleViewNoLogin();
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
