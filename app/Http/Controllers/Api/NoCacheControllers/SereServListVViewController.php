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
    protected $serviceReq;
    public function __construct(
        Request $request, 
        SereServListVViewService $sereServListVViewService, 
        SereServListVView $sereServListVView,
        ServiceReq $serviceReq,
        )
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sereServListVViewService = $sereServListVViewService;
        $this->sereServListVView = $sereServListVView;
        $this->serviceReq = $serviceReq;
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
            $this->serviceIds,
            $this->tab,
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
        if($this->treatmentCode == null && $this->treatmentId == null && $this->trackingId == null && $this->serviceReqId == null){
            return returnDataSuccess(null, []);
        }
        switch ($this->tab) {
            case 'suaChiDinh': // trong danh sách y lệnh => sửa
                //check y lệnh có bắt đầu chưa service_req_stt_code == 01
                // if ($this->serviceReqId) {
                //     $dataServiceReq = $this->serviceReq
                //     ->leftJoin('his_service_req_stt', 'his_service_req_stt.id', '=', 'his_service_req.service_req_stt_id')
                //     ->find($this->serviceReqId);
                //     if ($dataServiceReq) {
                //         if ($dataServiceReq->service_req_stt_code != '01') {
                //             $this->errors[$this->serviceReqIdName] = "Không thể sửa y lệnh khi y lệnh đã bắt đầu!";
                //         }
                //     } else {
                //         $this->errors[$this->serviceReqIdName] = "Không tìm thấy y lệnh!";
                //     }
                // } else {
                //     $this->errors[$this->serviceReqIdName] = "Thiếu mã y lệnh!";
                // }
                // if ($this->checkParam()) {
                //     return $this->checkParam();
                // }

                $data = $this->sereServListVViewService->handleDataBaseGetAllSuaChiDinh();
                break;
        case 'chonThongTinXuLy': // Lấy ra danh sách dịch vụ đã chọn của bệnh nhân và nhóm lại theo loại dịch vụ
                if (!$this->treatmentId) {
                    $this->errors[$this->treatmentIdName] = "Thiếu thông tin hồ sơ bệnh án!";
                }
                $data = $this->sereServListVViewService->handleDataBaseGetAllChonThongTinXuLy();
                break;
            default:
                $data = $this->sereServListVViewService->handleDataBaseGetAll();
                break;
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

        $data = $this->sereServListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
