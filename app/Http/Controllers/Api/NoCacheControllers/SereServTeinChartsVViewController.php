<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServTeinChartsVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\SereServTeinChartsVView;
use App\Services\Model\SereServTeinChartsVViewService;
use Illuminate\Http\Request;


class SereServTeinChartsVViewController extends BaseApiCacheController
{
    protected $sereServTeinChartsVViewService;
    protected $sereServTeinChartsVViewDTO;
    public function __construct(Request $request, SereServTeinChartsVViewService $sereServTeinChartsVViewService, SereServTeinChartsVView $sereServTeinChartsVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sereServTeinChartsVViewService = $sereServTeinChartsVViewService;
        $this->sereServTeinChartsVView = $sereServTeinChartsVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServTeinChartsVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServTeinChartsVViewDTO = new SereServTeinChartsVViewDTO(
            $this->sereServTeinChartsVViewName,
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
            $this->serviceCodes,
            $this->noCache,
        );
        $this->sereServTeinChartsVViewService->withParams($this->sereServTeinChartsVViewDTO);
    }
    public function index()
    {
        // Check xem người dùng có quyền lấy thông tin của patietnCode này không
        // $this->checkUserRoomPatientCode($this->patientCode);

        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if($this->patientCode == null){
            return returnDataSuccess(null, []);
        }
        $data = $this->sereServTeinChartsVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->sereServTeinChartsVView, $this->sereServTeinChartsVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->sereServTeinChartsVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
