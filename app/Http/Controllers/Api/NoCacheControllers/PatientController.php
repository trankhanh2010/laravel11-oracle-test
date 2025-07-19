<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\PatientDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Patient;
use App\Services\Model\PatientService;
use Illuminate\Http\Request;


class PatientController extends BaseApiCacheController
{
    protected $patientService;
    protected $patientDTO;
    public function __construct(Request $request, PatientService $patientService, Patient $patient)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patientService = $patientService;
        $this->patient = $patient;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->patient);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientDTO = new PatientDTO(
            $this->patientName,
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
            $this->phone,
            $this->cccdNumber,
            $this->patientCode,
        );
        $this->patientService->withParams($this->patientDTO);
    }
    public function timThongTinBenhNhan()
    {
        if ($this->phone == null) {
            $this->errors[$this->phoneName] = "Thiếu số điện thoại";
        }
        if ($this->cccdNumber == null) {
            $this->errors[$this->cccdNumberName] = "Thiếu số CCCD";
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->patientService->handleDataBaseGetAllTimThongTinBenhNhan();
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
    public function layThongTinBenhNhan()
    {
        if ($this->phone == null) {
            $this->errors[$this->phoneName] = "Thiếu số điện thoại";
        }
        if ($this->cccdNumber == null) {
            $this->errors[$this->cccdNumberName] = "Thiếu số CCCD";
        }
        if ($this->patientCode == null) {
            $this->errors[$this->patientCodeName] = "Thiếu mã bệnh nhân";
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->patientService->handleDataBaseGetAllLayThongTinBenhNhan();
        $paramReturn = [];
        return returnDataSuccess($paramReturn, $data);
    }

}
