<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\ResultClsVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\ResultClsVView;
use App\Services\Model\ResultClsVViewService;
use Illuminate\Http\Request;


class ResultClsVViewController extends BaseApiCacheController
{
    protected $resultClsVViewService;
    protected $resultClsVViewDTO;
    public function __construct(Request $request, ResultClsVViewService $resultClsVViewService, ResultClsVView $resultClsVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->resultClsVViewService = $resultClsVViewService;
        $this->resultClsVView = $resultClsVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->resultClsVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->resultClsVViewDTO = new ResultClsVViewDTO(
            $this->resultClsVViewName,
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
            $this->param,
            $this->noCache,
            $this->treatmentCode,
            $this->patientCode,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
        );
        $this->resultClsVViewService->withParams($this->resultClsVViewDTO);
    }
    public function index()
    {
        // Check xem người dùng có quyền lấy thông tin của treatment này không
        // $this->checkUserRoomTreatmentId(  
        //     $this->getTreatmentIdByServiceReqId($this->serviceReqId)
        //     ??  $this->getTreatmentIdBySereServId($this->sereServIds[0] ?? 0)
        //     ??  null
        // );
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if($this->treatmentCode == null && $this->patientCode == null){
            return returnDataSuccess(null, []);
        }
        if($this->keyword != null){
            $data = $this->resultClsVViewService->handleDataBaseSearch();
        }else{
            $data = $this->resultClsVViewService->handleDataBaseGetAll();
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

}
