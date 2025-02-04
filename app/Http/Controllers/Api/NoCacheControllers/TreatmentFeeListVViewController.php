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
    protected $twilioService;
    protected $otpService;
    public function __construct(
        Request $request,
        ElasticsearchService $elasticSearchService,
        TreatmentFeeListVViewService $treatmentFeeListVViewService,
        TreatmentFeeListVView $treatmentFeeListVView,
        TwilioService $twilioService,
        OtpService $otpService,
    ) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentFeeListVViewService = $treatmentFeeListVViewService;
        $this->treatmentFeeListVView = $treatmentFeeListVView;
        $this->twilioService = $twilioService;
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
        );
        $this->treatmentFeeListVViewService->withParams($this->treatmentFeeListVViewDTO);
    }
    public function index()
    {
        // Kiểm tra khoảng cách ngày
        if (($this->fromTime !== null) && ($this->toTime !== null)) {
            if (($this->toTime - $this->fromTime) > 60235959) {
                $this->errors[$this->fromTimeName] = 'Khoảng thời gian vượt quá 60 ngày!';
                $this->fromTime = null;
            }
        }
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
        $data = $this->treatmentFeeListVViewService->handleDataBaseGetAll();
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
            $phoneNumber = $data['data'][0]->patient_phone; // Lấy số điện thoại bệnh nhân
            $patientCode = $data['data'][0]->patient_code;
            $deviceInfo = request()->header('User-Agent'); // Lấy thông tin thiết bị từ User-Agent
            $ipAddress = request()->ip(); // Lấy địa chỉ IP
    
            // Gọi OtpService để xác thực OTP
            $otpVerified = $this->otpService->generateAndSendOtpTreatmentFee($phoneNumber, $patientCode, $deviceInfo, $ipAddress);
    
            if ($otpVerified) {
                $paramReturn[$this->authOtpName] = true;
            }
        }

        return returnDataSuccess($paramReturn, $data['data']);
    }
}
