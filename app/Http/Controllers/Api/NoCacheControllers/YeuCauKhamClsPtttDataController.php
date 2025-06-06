<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\YeuCauKhamClsPtttVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\YeuCauKhamClsPtttVView;
use App\Services\Model\ServiceRoomService;
use App\Services\Model\YeuCauKhamClsPtttVViewService;
use Illuminate\Http\Request;


class YeuCauKhamClsPtttDataController extends BaseApiCacheController
{
    protected $yeuCauKhamClsPtttVViewService;
    protected $yeuCauKhamClsPtttVViewDTO;
    public function __construct(
        Request $request, 
        YeuCauKhamClsPtttVViewService $yeuCauKhamClsPtttVViewService, 
        YeuCauKhamClsPtttVView $yeuCauKhamClsPtttVView,
        )
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->yeuCauKhamClsPtttVViewService = $yeuCauKhamClsPtttVViewService;
        $this->yeuCauKhamClsPtttVView = $yeuCauKhamClsPtttVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->yeuCauKhamClsPtttVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->yeuCauKhamClsPtttVViewDTO = new YeuCauKhamClsPtttVViewDTO(
            $this->yeuCauKhamClsPtttVViewName,
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
    $this->param,
            $this->noCache,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
            $this->intructionTimeDay,
            $this->intructionTimeMonth,
            $this->executeRoomId,
            $this->treatmentTypeIds,
            $this->serviceReqCode,
            $this->bedCode,
            $this->trangThai,
            $this->trangThaiVienPhi,
            $this->trangThaiKeThuoc,
            $this->kskContractId,
            $this->serviceIds,
            $this->tab,
        );
        $this->yeuCauKhamClsPtttVViewService->withParams($this->yeuCauKhamClsPtttVViewDTO);
    }
    public function show($serviceReqCode)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->yeuCauKhamClsPtttVViewService->handleDataBaseLayDuLieu($serviceReqCode);
        $paramReturn = [
            'ServiceReqCode' => $serviceReqCode,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
