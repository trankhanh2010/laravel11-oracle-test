<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\SereServTeinListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\SereServTeinListVView;
use App\Services\Model\SereServTeinListVViewService;
use Illuminate\Http\Request;


class SereServTeinListVViewController extends BaseApiCacheController
{
    protected $sereServTeinListVViewService;
    protected $sereServTeinListVViewDTO;
    public function __construct(Request $request, SereServTeinListVViewService $sereServTeinListVViewService, SereServTeinListVView $sereServTeinListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->sereServTeinListVViewService = $sereServTeinListVViewService;
        $this->sereServTeinListVView = $sereServTeinListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->sereServTeinListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->sereServTeinListVViewDTO = new SereServTeinListVViewDTO(
            $this->sereServTeinListVViewName,
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
            $this->serviceReqId,
            $this->groupBy,
            $this->sereServIds,
            $this->param,
            $this->noCache,
        );
        $this->sereServTeinListVViewService->withParams($this->sereServTeinListVViewDTO);
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
        if($this->serviceReqId == null && $this->sereServIds == null){
            return returnDataSuccess(null, []);
        }
        $data = $this->sereServTeinListVViewService->handleDataBaseGetAll();

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
            $validationError = $this->validateAndCheckId($id, $this->sereServTeinListVView, $this->sereServTeinListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->sereServTeinListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
