<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentBedRoomListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\TreatmentBedRoomListVView;
use App\Services\Model\TreatmentBedRoomListVViewService;
use DateTime;
use Illuminate\Http\Request;


class TreatmentBedRoomListVViewController extends BaseApiCacheController
{
    protected $treatmentBedRoomListVViewService;
    protected $treatmentBedRoomListVViewDTO;
    public function __construct(Request $request, TreatmentBedRoomListVViewService $treatmentBedRoomListVViewService, TreatmentBedRoomListVView $treatmentBedRoomListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatmentBedRoomListVViewService = $treatmentBedRoomListVViewService;
        $this->treatmentBedRoomListVView = $treatmentBedRoomListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->treatmentBedRoomListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentBedRoomListVViewDTO = new TreatmentBedRoomListVViewDTO(
            $this->treatmentBedRoomListVViewName,
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
            $this->departmentCode,
            $this->isInBed,
            $this->bedRoomIds,
            $this->treatmentTypeIds,
            $this->isCoTreatDepartment,
            $this->patientClassifyIds,
            $this->isOut,
            $this->addLoginname,
            $this->addTimeFrom,
            $this->addTimeTo,
            $this->groupBy,
            $this->executeRoomCode,
            $this->executeRoomIds,
            $this->treatmentCode,
            $this->patientCode,
            $this->isCount,
            $this->param,
            $this->noCache,
        );
        $this->treatmentBedRoomListVViewService->withParams($this->treatmentBedRoomListVViewDTO);
    }
    public function index()
    {
        // Nếu không có ngày
        if (
            $this->isInBed === null &&
            $this->treatmentCode == null &&
            $this->patientCode == null &&
            ($this->addTimeFrom == null || $this->addTimeTo == null)
        ) {
            $this->errors[$this->addTimeFromName] = "Thiếu thời gian";
            $this->errors[$this->addTimeToName] = "Thiếu thời gian";
        } else {
            // Nếu quá 180 ngày
            $from = DateTime::createFromFormat('YmdHis', $this->addTimeFrom ?? "");
            $to = DateTime::createFromFormat('YmdHis', $this->addTimeTo ?? "");
            if ($from && $to) {
                $diff = $from->diff($to)->days;
                if ($diff > 365) {
                    $this->errors[$this->addTimeFromName] = "Thời gian lọc quá 365 ngày";
                    $this->errors[$this->addTimeToName] = "Thời gian lọc quá 365 ngày";
                }
            }
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        // Nếu k có danh sách id phòng thì k trả kết quả
        if ($this->bedRoomIds == null) {
            return returnDataSuccess(null, []);
        }
        $data = $this->treatmentBedRoomListVViewService->handleDataBaseSearch();

        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->totalPageName => $data['count'] ? ceil($data['count'] / $this->limit) : null,
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentBedRoomListVView, $this->treatmentBedRoomListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }

        $data = $this->treatmentBedRoomListVViewService->handleDataBaseGetWithId($id);

        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
