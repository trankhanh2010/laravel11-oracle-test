<?php

namespace App\Http\Controllers\Api\GuestControllers;

use App\DTOs\DangKyKhamDTO;
use App\DTOs\OtpDTO;
use App\Http\Controllers\Controller;
use App\Services\Guest\DangKyKhamService;
use Illuminate\Http\Request;
use App\Http\Requests\DangKyKham\DangKyKhamRequest;
use App\Http\Requests\DangKyKham\HuyDangKyDichVuRequest;
use App\Models\HIS\Patient;
use App\Models\HIS\SereServ;
use App\Services\Auth\OtpService;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\MessageBag;

class DangKyKhamController extends Controller
{
    protected $dangKyKhamService;
    protected $dangKyKhamDTO;
    protected $otpDTO;
    protected $patient;
    protected $sereServ;
    protected $otpService;
    protected $notificationService;
    protected $errors;
    public function __construct(
        // DangKyKhamRequest $request,
        DangKyKhamService $dangKyKhamService,
        Patient $patient,
        SereServ $sereServ,
        OtpService $otpService,
        NotificationService $notificationService,
    ) {
        $this->dangKyKhamService = $dangKyKhamService;
        $this->patient = $patient;
        $this->sereServ = $sereServ;
        $this->otpService = $otpService;
        $this->notificationService = $notificationService;
        $this->errors = new MessageBag();
    }
    public function dangKyKham(DangKyKhamRequest $request)
    {
        $this->validateRequestPhone($request);
        try {
            // Thêm tham số vào service
            $this->dangKyKhamDTO = new DangKyKhamDTO(
                $request,
            );
            $this->dangKyKhamService->withParams($this->dangKyKhamDTO);
            $data = $this->dangKyKhamService->handleDangKyKham();
            $data = $data['Data'];
            $paramReturn = [];

            // Gửi thông báo đăng ký thành công
            $resultDispatchJobGuiThongBao = $this->notificationService->sendDangKyKhamThanhCong($data);
            return returnDataSuccess($paramReturn, $data);
        } catch (\Throwable $e) {
            return writeAndThrowError($e->getMessage(), $e); // Lấy lỗi tự thêm
        }
    }
    public function huyDangKyDichVu(HuyDangKyDichVuRequest $request)
    {
        $this->validateSereServ($request);
        try {
            // Thêm tham số vào service
            $this->dangKyKhamDTO = new DangKyKhamDTO(
                $request,
            );
            $this->dangKyKhamService->withParams($this->dangKyKhamDTO);
            $data = $this->dangKyKhamService->handleHuyDangKyDichVu();
            $data = $data['Data'];
            $paramReturn = [];

            return returnDataSuccess($paramReturn, $data);
        } catch (\Throwable $e) {
            return writeAndThrowError($e->getMessage(), $e); // Lấy lỗi tự thêm
        }
    }
    // Ném lỗi trong $this->errors
    public function throwException(){
    // Nếu có lỗi, ném ra giống như trong FormRequest
        if ($this->errors->isNotEmpty()) {
            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Dữ liệu không hợp lệ!',
                'data'      => $this->errors->toArray()
            ], 422));
        }
    }
    // Kiểm tra xác thực trước khi vào service
    public function validateRequestPhone($request)
    {
        if (!empty($request->patientId) && $request->patientId != 0) {
            $this->validatePatientPhone($request);
        } else {
            $this->validateRegisterPhone($request);
        }
        $this->throwException();
    }
    // Xác thực số đăng ký mới
    public function validateRegisterPhone($request)
    {
        // Nếu đăng ký mới thì xác thực sđt => lưu cache cho sđt đó
        $this->otpDTO = new OtpDTO('', '', $request->phone);
        $this->otpService->withParams($this->otpDTO);
        $otpVerified = $this->otpService->isVerified();
        if (!$otpVerified) {
            $this->errors->add('verifyOtpRegisterPhone', 'Chưa xác thực OTP cho số đăng ký mới ' . convertPhoneToLocalFormat($request->phone) . '!');
        }
    }
    // Xác thực bệnh nhân
    public function validatePatient($dataPatient)
    {
        $patientCode = $dataPatient->patient_code;
        // Thêm tham số vào service
        $this->otpDTO = new OtpDTO($patientCode,);
        $this->otpService->withParams($this->otpDTO);
        $otpVerified = $this->otpService->isVerified();
        if (!$otpVerified) {
            $this->errors->add('verifyOtp', 'Chưa xác thực OTP!');
        }
    }
    public function getDataPatient($patientId)
    {
        $dataPatient = $this->patient->where('is_delete', 0)->find($patientId);
        if (empty($dataPatient)) {
            $this->errors->add('patientId', 'Không tìm thấy thông tin bệnh nhân!');
        } 
        return $dataPatient;
    }
    // Xác thực số của bệnh nhân gửi lên
    public function validatePatientPhone($request)
    {
        $dataPatient = $this->getDataPatient($request->patientId);
        if (!empty($dataPatient)) {
            $isNotChangePatientPhone = convertPhoneTo84Format($request->phone) == convertPhoneTo84Format($dataPatient->phone);
            if ($isNotChangePatientPhone) {
                // Nếu không thay số điện thoại mới
                $this->validatePatient($dataPatient);
            } else {
                $this->validateRegisterPhone($request);
            }
        }
    }
    // validate sereServ muốn xóa
    public function validateSereServ($request)
    {
        $dataSereServ = $this->sereServ
        ->where('is_delete', 0)
        ->where(function ($q) {
            $q->where('is_no_execute', 0)
            ->orWhereNull('is_no_execute');
        })
        ->find($request->deleteSereServId);
        if (empty($dataSereServ) || empty($dataSereServ->tdl_patient_id) || empty($dataSereServ->service_req_id)) {
            $this->errors->add('sereServId', 'Id dịch vụ muốn hủy không hợp lệ!');
        } else {
            $request->deleteServiceReqId = $dataSereServ->service_req_id;
            $dataPatient = $this->getDataPatient($dataSereServ->tdl_patient_id);
            if (!empty($dataPatient)) {
                $this->validatePatient($dataPatient);
            }
        }
        $this->throwException();
    }
}
