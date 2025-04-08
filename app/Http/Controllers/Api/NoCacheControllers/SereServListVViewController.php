<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\Tracking;
use App\Models\View\SereServListVView;
use App\Services\Model\SereServListVViewService;
use Illuminate\Http\Request;


class SereServListVViewController extends BaseApiCacheController
{
    protected $sereServListVViewService;
    protected $sereServListVViewDTO;
    public function __construct(Request $request, SereServListVViewService $sereServListVViewService, SereServListVView $sereServListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
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
            $this->noCache,
            $this->treatmentCode,
        );
        $this->sereServListVViewService->withParams($this->sereServListVViewDTO);
    }
    public function index()
    {
        // Check xem người dùng có quyền lấy thông tin của treatment này không
        // $this->checkUserRoomTreatmentId($this->treatmentId 
        //     ??  $this->getTreatmentIdByTrackingId($this->trackingId)
        //     ??  $this->getTreatmentIdByServiceReqId($this->serviceReqId)
        //     ??  null
        // );
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->sereServListVViewService->handleDataBaseGetAll();
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

        $data = $this->sereServListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
