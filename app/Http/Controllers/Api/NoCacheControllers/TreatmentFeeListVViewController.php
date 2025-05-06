<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TreatmentFeeListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentFeeListVView\CreateTreatmentFeeListVViewRequest;
use App\Http\Requests\TreatmentFeeListVView\UpdateTreatmentFeeListVViewRequest;
use App\Models\View\TreatmentFeeListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentFeeListVViewService;
use App\Services\Auth\OtpService;
use App\Services\Sms\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TreatmentFeeListVViewController extends BaseApiCacheController
{
    protected $treatmentFeeListVViewService;
    protected $treatmentFeeListVViewDTO;
    protected $otpService;
    public function __construct(
        Request $request,
        TreatmentFeeListVViewService $treatmentFeeListVViewService,
        TreatmentFeeListVView $treatmentFeeListVView,
        OtpService $otpService,
    ) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->treatmentFeeListVViewService = $treatmentFeeListVViewService;
        $this->treatmentFeeListVView = $treatmentFeeListVView;
        $this->otpService = $otpService;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->treatmentFeeListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        if (($this->treatmentCode != null || $this->patientCode != null)) {
            $this->cursorPaginate = false;
            $this->getAll = true;
        }
        // Thêm tham số vào service
        $this->treatmentFeeListVViewDTO = new TreatmentFeeListVViewDTO(
            $this->treatmentFeeListVViewName,
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
            $this->fromTime,
            $this->toTime,
            $this->executeDepartmentCode,
            $this->lastId,
            $this->cursorPaginate,
            $this->treatmentCode,
            $this->patientCode,
            $this->status,
            $this->patientPhone,
            $this->param,
            $this->noCache,
            $this->treatmentTypeCodes,
            $this->patientTypeCodes,
            $this->endDepartmentCodes,
            $this->outTimeFrom,
            $this->outTimeTo,
        );
        $this->treatmentFeeListVViewService->withParams($this->treatmentFeeListVViewDTO);
    }
    public function index()
    {
        // Kiểm tra khoảng cách ngày
        // if (($this->fromTime !== null) && ($this->toTime !== null)) {
        //     if (($this->toTime - $this->fromTime) > 60235959) {
        //         $this->errors[$this->fromTimeName] = 'Khoảng thời gian vượt quá 60 ngày!';
        //         $this->fromTime = null;
        //     }
        // }
        if ($this->treatmentCode == null && $this->patientCode == null) {
            if (($this->fromTime == null) && ($this->toTime == null) && (!$this->cursorPaginate)) {
                $this->errors[$this->fromTimeName] = 'Thiếu thời gian!';
                $this->errors[$this->toTimeName] = 'Thiếu thời gian!';
            }
        }

        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if($keyword != null){
            $data = $this->treatmentFeeListVViewService->handleDataBaseSearch();
        }else{
            $data = $this->treatmentFeeListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentFeeListVView, $this->treatmentFeeListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }

        $data = $this->treatmentFeeListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }

    public function viewNoLogin()
    {

        if ($this->checkParam()) {
            return $this->checkParam();
        }

        $data = $this->treatmentFeeListVViewService->handleViewNoLogin();
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->keywordName => $this->keyword,
            $this->orderByName => $this->orderByRequest,

            // Xác thực OTP
            // true => k xác thực
            // false => cần xác thực
            $this->authOtpName => false,

        ];

        // nếu có dữ liệu
        if ($data['count'] > 0) {

            $patientCode = $data['data'][0]->patient_code;
            $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
            $ipAddress = request()->ip(); // Lấy địa chỉ IP
    
            // Gọi OtpService để xác thực OTP
            $otpVerified = $this->otpService->isOtpTreatmentFeeVerified( $patientCode, $deviceInfo, $ipAddress);
    
           
            if ($otpVerified) {
                $paramReturn[$this->authOtpName] = true;
            }else{
                // Hàm để giữ 2 ký tự đầu và cuối, còn lại thay bằng dấu *
                function maskPhone($value) {
                    if (strlen($value) > 6) {
                        return substr($value, 0, 3) . str_repeat('*', strlen($value) - 6) . substr($value, -3);
                    }
                    return $value; // Nếu độ dài < 6, không thay đổi
                }
                // Lọc các trường cần thiết từ mỗi item trong data
                $filteredData = $data['data']->map(function ($item) {
                    return [
                        'patientCode' => $item->patient_code,
                        'treatmentCode' => $this->treatmentCode ?? $item->treatment_code,
                        'patientPhone' => maskPhone($item->patient_phone),
                        'patientMobile' => maskPhone($item->patient_mobile),
                        'patientEmail' => maskPhone($item->patient_email),
                        'patientRelativePhone' => maskPhone($item->patient_relative_phone),
                        'patientRelativeMobile' => maskPhone($item->patient_relative_mobile),
                    ];
                });
                $data['data'] = $filteredData;
            }
        }

        return returnDataSuccess($paramReturn, $data['data']);
    }
}
