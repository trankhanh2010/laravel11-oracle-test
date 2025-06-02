<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\YeuCauKhamClsPtttVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\YeuCauKhamClsPtttVView;
use App\Services\Model\YeuCauKhamClsPtttVViewService;
use Illuminate\Http\Request;


class YeuCauKhamClsPtttVViewController extends BaseApiCacheController
{
    protected $yeuCauKhamClsPtttVViewService;
    protected $yeuCauKhamClsPtttVViewDTO;
    public function __construct(Request $request, YeuCauKhamClsPtttVViewService $yeuCauKhamClsPtttVViewService, YeuCauKhamClsPtttVView $yeuCauKhamClsPtttVView)
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
        );
        $this->yeuCauKhamClsPtttVViewService->withParams($this->yeuCauKhamClsPtttVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword) {
            $data = $this->yeuCauKhamClsPtttVViewService->handleDataBaseSearch();
        } else {
            $data = $this->yeuCauKhamClsPtttVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->yeuCauKhamClsPtttVView, $this->yeuCauKhamClsPtttVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->yeuCauKhamClsPtttVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
