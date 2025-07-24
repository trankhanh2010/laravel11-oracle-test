<?php

namespace App\Http\Controllers\Api\GuestControllers;

use App\DTOs\DangKyKhamDTO;
use App\DTOs\OtpDTO;
use App\Http\Controllers\Controller;
use App\Services\Guest\DangKyKhamService;
use Illuminate\Http\Request;
use App\Http\Requests\DangKyKham\DangKyKhamRequest;
use App\Models\HIS\Patient;
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
    protected $otpService;
    protected $notificationService;
    public function __construct(
        // DangKyKhamRequest $request,
        DangKyKhamService $dangKyKhamService,
        Patient $patient,
        OtpService $otpService,
        NotificationService $notificationService,
    ) {
        $this->dangKyKhamService = $dangKyKhamService;
        $this->patient = $patient;
        $this->otpService = $otpService;
        $this->notificationService = $notificationService;
    }
    public function dangKyKham(DangKyKhamRequest $request)
    {
        $errors = new MessageBag();

        if (!empty($request->patientId) && $request->patientId != 0) {
            $dataPatient = $this->patient->where('is_delete', 0)->find($request->patientId);
            if (empty($dataPatient)) {
                $errors->add('patientId', 'Không tìm thấy thông tin bệnh nhân!');
            } else {
                $patientCode = $dataPatient->patient_code;
                // Thêm tham số vào service
                $this->otpDTO = new OtpDTO($patientCode,);
                $this->otpService->withParams($this->otpDTO);
                $otpVerified = $this->otpService->isVerified();
                if (!$otpVerified) {
                    $errors->add('verifyOtp', 'Chưa xác thực OTP!');
                }
            }
        }else{
            // Nếu đăng ký mới thì xác thực sđt => lưu cache cho sđt đó
            $this->otpDTO = new OtpDTO('', '', $request->phone);
            $this->otpService->withParams($this->otpDTO);
            $otpVerified = $this->otpService->isVerified();
                if (!$otpVerified) {
                    $errors->add('verifyOtpRegisterPhone', 'Chưa xác thực OTP cho số ' .convertPhoneToLocalFormat($request->phone). '!');
                }
        }

        // Nếu có lỗi, ném ra giống như trong FormRequest
        if ($errors->isNotEmpty()) {
            throw new HttpResponseException(response()->json([
                'success'   => false,
                'message'   => 'Dữ liệu không hợp lệ!',
                'data'      => $errors->toArray()
            ], 422));
        }

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
}
