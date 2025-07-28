<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TrackingListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\TrackingListVView;
use App\Services\Model\TrackingListVViewService;
use Illuminate\Http\Request;


class TrackingListVViewController extends BaseApiCacheController
{
    protected $trackingListVViewService;
    protected $trackingListVViewDTO;
    public function __construct(Request $request, TrackingListVViewService $trackingListVViewService, TrackingListVView $trackingListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->trackingListVViewService = $trackingListVViewService;
        $this->trackingListVView = $trackingListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->trackingListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->trackingListVViewDTO = new TrackingListVViewDTO(
            $this->trackingListVViewName,
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
            $this->groupBy,
            $this->param,
            $this->noCache,
            $this->treatmentCode,
            $this->tab,
        );
        $this->trackingListVViewService->withParams($this->trackingListVViewDTO);
    }
    public function index()
    {
        // Check xem người dùng có quyền lấy thông tin của treatment này không
        // $this->checkUserRoomTreatmentId($this->treatmentId);

        if ($this->checkParam()) {
            return $this->checkParam();
        }
        switch ($this->tab) {
            case 'danhSachToDieuTriCu': // bỏ
                $data = $this->trackingListVViewService->handleDataBaseGetAllDanhSachToDieuTriCu();
                break;
            case 'danhSachTheoKhoaDieuTri':
                $data = $this->trackingListVViewService->handleDataBaseGetAllDanhSachTheoKhoaDieuTri();
                break;
            default:
                $data = $this->trackingListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->trackingListVView, $this->trackingListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->trackingListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
