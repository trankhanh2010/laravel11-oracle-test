<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\PatientDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Patient;
use App\Services\Auth\OtpService;
use App\Services\Model\PatientService;
use Illuminate\Http\Request;


class PatientController extends BaseApiCacheController
{
    protected $patientService;
    protected $patientDTO;
    protected $otpService;
    public function __construct(
        Request $request, 
        PatientService $patientService, 
        Patient $patient,
        OtpService $otpService,
        )
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->patientService = $patientService;
        $this->patient = $patient;
        $this->otpService = $otpService;
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
        if ($this->phone == null && $this->cccdNumber == null) {
            $this->errors[$this->phoneName] = "Thiếu số điện thoại";
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
        // if ($this->phone == null) {
        //     $this->errors[$this->phoneName] = "Thiếu số điện thoại";
        // }
        // if ($this->cccdNumber == null) {
        //     $this->errors[$this->cccdNumberName] = "Thiếu số CCCD";
        // }
        if ($this->patientCode == null) {
            $this->errors[$this->patientCodeName] = "Thiếu mã bệnh nhân";
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->patientService->handleDataBaseGetAllLayThongTinBenhNhan();
        $paramReturn = [];
        if($data){
            $patientCode = $data->patient_code;
            $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
            $ipAddress = request()->ip(); // Lấy địa chỉ IP
    
            // Gọi OtpService để xác thực OTP
            $otpVerified = $this->otpService->isOtpTreatmentFeeVerified( $patientCode, $deviceInfo, $ipAddress);
            $paramReturn[$this->verifyOtpName] = $otpVerified;
            if (!$otpVerified){
                // Hàm để giữ 2 ký tự đầu và cuối, còn lại thay bằng dấu *
                function maskPhone($value) {
                    if (strlen($value) > 6) {
                        return substr($value, 0, 3) . str_repeat('*', strlen($value) - 6) . substr($value, -3);
                    }
                    return $value; // Nếu độ dài < 6, không thay đổi
                }
                // Lọc các trường cần thiết từ mỗi item trong data
                $filteredData  = [
                        'patientCode' => $data->patient_code,
                        'patientPhone' => maskPhone($data->phone),
                        'patientMobile' => maskPhone($data->mobile),
                        'patientEmail' => maskPhone($data->email),
                        'patientRelativePhone' => maskPhone($data->relative_phone),
                        'patientRelativeMobile' => maskPhone($data->relative_mobile),
                    ];
                $data = $filteredData;
            }
        }
        return returnDataSuccess($paramReturn, $data);
    }

}
