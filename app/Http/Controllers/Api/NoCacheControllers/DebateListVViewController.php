<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\DebateListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\DebateListVView;
use App\Services\Model\DebateListVViewService;
use Illuminate\Http\Request;


class DebateListVViewController extends BaseApiCacheController
{
    protected $debateListVViewService;
    protected $debateListVViewDTO;
    public function __construct(Request $request, DebateListVViewService $debateListVViewService, DebateListVView $debateListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->debateListVViewService = $debateListVViewService;
        $this->debateListVView = $debateListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->debateListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->debateListVViewDTO = new DebateListVViewDTO(
            $this->debateListVViewName,
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
            $this->treatmentCode,
            $this->departmentIds,
            $this->debateTimeFrom,
            $this->debateTimeTo,
            $this->param,
            $this->noCache,
        );
        $this->debateListVViewService->withParams($this->debateListVViewDTO);
    }
    public function index()
    {
        // Check xem người dùng có quyền lấy thông tin của treatment này không
        // $this->checkUserRoomTreatmentId($this->treatmentId);
        if ($this->checkParam()) {
            return $this->checkParam();
        }

        $data = $this->debateListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->debateListVView, $this->debateListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->debateListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
