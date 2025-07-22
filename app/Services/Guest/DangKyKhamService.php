<?php

namespace App\Services\Guest;

use App\DTOs\DangKyKhamDTO;
use App\Models\HIS\BloodAbo;
use App\Models\HIS\BloodRh;
use App\Models\HIS\Career;
use App\Models\HIS\HospitalizeReason;
use App\Models\HIS\Patient;
use App\Models\HIS\PatientClassify;
use App\Models\HIS\WorkPlace;
use App\Models\SDA\Commune;
use App\Models\SDA\Ethnic;
use App\Models\SDA\National;
use App\Models\SDA\Province;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use App\Events\Cache\DeleteCache;
use App\Models\HIS\PatientType;
use App\Models\HIS\ServiceRoom;
use App\Models\HIS\TreatmentType;
use App\Models\View\RoomVView;

class DangKyKhamService
{
    protected $taiKhoanMacDinh; // tài khoản mặc định thực hiện thao tác đăng ký khám
    protected $cacheKeyTaiKhoanMacDinh; // key lưu cache tài khoản mặc định
    protected $roomCodeMacDinh; // mã của requestRoom mặc định
    protected $roomIdMacDinh; // id requestRoom mặc định
    protected $departmentIdRoomIdMacDinh; // lấy departmentId của room mặc định
    protected $branchIdRoomIdMacDinh; // branchId của room mặc định
    protected $treatmentTypeIdMacDinh; // mặc định Khám
    protected $patientTypeIdMacDinh; // mặc định Viện phí
    protected $timeCacheMacDinh; // thời gian cache redis mặc định
    protected $urlAcs;
    protected $urlMos;
    protected $apiDangKyKham;
    protected $apiDangKyThongTin;
    protected $apiDangNhap;
    protected $apiDangKyPhienLamViec;
    protected $cacheKeySetting;
    protected $cacheKeyGuest;
    protected $usernameTaiKhoanMacDinh; // tên đăng nhập tk mặc định
    protected $passwordTaiKhoanMacDinh; // mật khẩu đăng nhập tk mặc định
    protected $params;
    protected $patient;
    protected $national;
    protected $ethnic;
    protected $province;
    protected $commune;
    protected $career;
    protected $workPlace;
    protected $bloodAbo;
    protected $bloodRh;
    protected $patientClassify;
    protected $hospitalizeReason;
    protected $roomVView;
    protected $treatmentType;
    protected $patientType;
    protected $serviceRoom;
    public function __construct(
        Patient $patient,
        National $national,
        Ethnic $ethnic,
        Province $province,
        Commune $commune,
        Career $career,
        WorkPlace $workPlace,
        BloodAbo $bloodAbo,
        BloodRh $bloodRh,
        PatientClassify $patientClassify,
        HospitalizeReason $hospitalizeReason,
        RoomVView $roomVView,
        TreatmentType $treatmentType,
        PatientType $patientType,
        ServiceRoom $serviceRoom,
    ) {
        $this->cacheKeyTaiKhoanMacDinh = 'thong_tin_dang_nhap_tai_khoan_mac_dinh_dang_ky_kham';
        $this->urlAcs = config('database')['connections']['acs']['acs_url'];
        $this->urlMos = config('database')['connections']['mos']['mos_url'];
        $this->apiDangKyKham = $this->urlMos . '/api/HisServiceReq/ExamRegister';
        $this->apiDangKyThongTin = $this->urlMos .'/api/HisPatient/RegisterProfile';
        $this->apiDangKyPhienLamViec = $this->urlMos . '/api/Token/UpdateWorkInfo';
        $this->apiDangNhap = $this->urlAcs . '/api/Token/Login';
        $this->cacheKeySetting = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $this->cacheKeyGuest = "cache_keys:" . "guest"; // Set để lưu danh sách key
        $this->usernameTaiKhoanMacDinh = config('database')['connections']['guest']['dang_ky_kham']['tai_khoan_mac_dinh']['username'];
        $this->passwordTaiKhoanMacDinh = config('database')['connections']['guest']['dang_ky_kham']['tai_khoan_mac_dinh']['password'];
        // Không có tài khoản hoặc mật khẩu => ném lỗi
        if (empty($this->usernameTaiKhoanMacDinh) || empty($this->passwordTaiKhoanMacDinh)) {
            throw new \Exception('Thiếu thông tin đăng nhập cho tài khoản mặc định.');
        }
        // Nối thêm chuỗi cho key cache redis
        $this->cacheKeyTaiKhoanMacDinh = $this->cacheKeyTaiKhoanMacDinh . '_' . $this->usernameTaiKhoanMacDinh;

        $this->roomCodeMacDinh = config('database')['connections']['guest']['dang_ky_kham']['request_room_code_mac_dinh'];
        $this->timeCacheMacDinh = now()->addMinutes(43200); // 30 ngày
        $this->patient = $patient;
        $this->national = $national;
        $this->ethnic = $ethnic;
        $this->province = $province;
        $this->commune = $commune;
        $this->career = $career;
        $this->workPlace = $workPlace;
        $this->bloodAbo = $bloodAbo;
        $this->bloodRh = $bloodRh;
        $this->patientClassify = $patientClassify;
        $this->hospitalizeReason = $hospitalizeReason;
        $this->roomVView = $roomVView;
        $this->treatmentType = $treatmentType;
        $this->patientType = $patientType;
        $this->serviceRoom = $serviceRoom;


        // lấy cache roomId mặc định
        $cacheKey = 'room_dang_ky_kham_benh_mac_dinh_room_id_' . $this->roomCodeMacDinh;
        $this->roomIdMacDinh = (int) Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data =  $this->roomVView->where('room_code', $this->roomCodeMacDinh)->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        // Không có phòng mặc định thì ném ra lỗi
        if (!$this->roomIdMacDinh) {
            throw new \Exception('Không có thông tin phòng làm việc mặc định.');
        }



        // lấy cache departmentId của room mặc định
        $cacheKey = 'room_dang_ky_kham_benh_mac_dinh_department_id_' . $this->roomCodeMacDinh;
        $this->departmentIdRoomIdMacDinh = (int) Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data =  $this->roomVView->where('room_code', $this->roomCodeMacDinh)->get();
            return $data->value('department_id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        // Không có khoa của phòng mặc định thì ném ra lỗi
        if (!$this->departmentIdRoomIdMacDinh) {
            throw new \Exception('Không có thông tin Khoa của phòng làm việc mặc định.');
        }



        // lấy cache branchId của room mặc định
        $cacheKey = 'room_dang_ky_kham_benh_mac_dinh_branch_id_' . $this->roomCodeMacDinh;
        $this->branchIdRoomIdMacDinh = (int) Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data =  $this->roomVView->where('room_code', $this->roomCodeMacDinh)->get();
            return $data->value('branch_id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        // Không có cơ sở của phòng mặc định thì ném ra lỗi
        if (!$this->branchIdRoomIdMacDinh) {
            throw new \Exception('Không có thông tin Cơ sở của phòng làm việc mặc định.');
        }



        // lấy cache treatmentTypeId mặc định
        $cacheKey = 'treatment_type_dang_ky_kham_mac_dinh_id';
        $this->treatmentTypeIdMacDinh = (int) Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data =  $this->treatmentType->where('treatment_type_code', '01')->get(); // mặc định là Khám
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);



        // lấy cache patientTypeId mặc định
        $cacheKey = 'patient_type_dang_ky_kham_mac_dinh_id';
        $this->patientTypeIdMacDinh = (int) Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data =  $this->patientType->where('patient_type_code', '02')->get(); // mặc định là Viện phí
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
    }
    public function withParams(DangKyKhamDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    private function callApiMos($rawBody){
        if(empty($this->params->request->serviceReqDetails) && ($this->params->request->patientId == 0)){ // Nếu đăng ký mới và k chọn dịch vụ => /api/HisPatient/RegisterProfile
            return $this->callApiDangKyThongTin($rawBody);
        }else{
            return $this->callApiDangKyKham($rawBody);
        }
    }
    private function callApiDangKyKham($rawBody)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->taiKhoanMacDinh['Data']['TokenCode'],
            ])->post($this->apiDangKyKham, $rawBody);

            $data = $response->json();
        } catch (\Throwable $e) {
            throw new \Exception("Lỗi gọi API đăng ký khám!");
        }
        if (!$data['Success']) {
            throw new \Exception("Đăng ký khám không thành công!");
        }
        return $data;
    }
    private function callApiDangKyThongTin($rawBody)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->taiKhoanMacDinh['Data']['TokenCode'],
            ])->post($this->apiDangKyThongTin, $rawBody);

            $data = $response->json();
        } catch (\Throwable $e) {
            throw new \Exception("Lỗi gọi API đăng ký thông tin!");
        }
        if (!$data['Success']) {
            throw new \Exception("Đăng ký thông tin không thành công!");
        }
        return $data;
    }
    private function callApiDangNhapTaiKhoanMacDinhChoDangKyKham()
    {
        $username = 'HIS:' . $this->usernameTaiKhoanMacDinh;
        $password = $this->passwordTaiKhoanMacDinh;

        $response = Http::withBasicAuth($username, $password)->get($this->apiDangNhap);

        $data = $response->json();
        if (!$data['Success']) {
            throw new \Exception("Lỗi gọi API lấy tài khoản mặc định đăng ký khám!");
        }
        return $data;
    }
    private function callApiDangKyPhienLamViecChoPhongMacDinh()
    {
        $rawBody = [
            "CommonParam" => [
                "Messages" => [],
                "BugCodes" => [],
                "MessageCodes" => [],
                "Start" => null,
                "Limit" => null,
                "Count" => null,
                "ModuleCode" => null,
                "LanguageCode" => "VI",
                "Now" => 0,
                "HasException" => false
            ],
            "ApiData" => [
                "RoomIds" => null,
                "Rooms" => [
                    [
                        "RoomId" => $this->roomIdMacDinh,
                        "DeskId" => null
                    ],
                ],
                "WorkingShiftId" => null,
                "NurseLoginName" => null,
                "NurseUserName" => null
            ]
        ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->taiKhoanMacDinh['Data']['TokenCode'],
        ])->post($this->apiDangKyPhienLamViec, $rawBody);
        $data = $response->json();
        if (!$data['Success']) {
            throw new \Exception("Lỗi gọi API đăng ký phiên làm việc cho tài khoản mặc định đăng ký khám!");
        }
    }
    private function layTaiKhoanMacDinhChoDangKyKham()
    {
        try {
            // lưu 6 ngày
            $this->taiKhoanMacDinh = Cache::remember($this->cacheKeyTaiKhoanMacDinh, now()->addMinutes(8640), function () {
                $data =  $this->callApiDangNhapTaiKhoanMacDinhChoDangKyKham();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($this->cacheKeySetting, [$this->cacheKeyTaiKhoanMacDinh]);
            Redis::connection('cache')->sadd($this->cacheKeyGuest, [$this->cacheKeyTaiKhoanMacDinh]);
        } catch (\Throwable $e) {
            throw new \Exception("Lỗi khi lấy tài khoản mặc định đăng ký khám!");
        }
    }
    private function xoaCacheTaiKhoanMacDinhChoDangKyKham()
    {
        event(new DeleteCache("guest"));
    }
    private function dangKyPhienLamViecChoPhongMacDinh()
    {
        try {
            $this->callApiDangKyPhienLamViecChoPhongMacDinh();
        } catch (\Throwable $e) {
            $this->xoaCacheTaiKhoanMacDinhChoDangKyKham();
            throw new \Exception('Không thể đăng ký phiên làm việc cho phòng mặc định.');
        }
    }
    private function validate(){
        $this->validateTreDuoi6Tuoi();
    }
    private function validateTreDuoi6Tuoi(){
        $tuoiTheoNam = getTuoi($this->params->request->dob)['01'];
        if($tuoiTheoNam < 6){
            if(
                empty($this->params->request->relativeType)
                || empty($this->params->request->relativeName)
                || empty($this->params->request->motherName)
                || empty($this->params->request->fatherName)
            ){
                throw new \Exception('Trẻ dưới 6 tuổi bắt buộc phải nhập các thông tin người thân.');
            }
        }
    }
    private function getHisPatientArrayRequest()
    {
        $data = [];
        $arrayHoVaTen = $this->tachHoVaTen();
        // lưu cache redis
        $dataNational = $this->getCacheNational();
        $dataEthnic = $this->getCacheEthnic();
        $dataProvince = $this->getCacheProvince();
        $dataCommune = $this->getCacheCommune();
        $dataCareer = $this->getCacheCareer();
        // $dataBloodAbo = $this->getCacheBloodAbo();
        // $dataBloodRh = $this->getCacheBloodRh();


        $data = [
            "ID" => $this->params->request->patientId,
            "FIRST_NAME" => $arrayHoVaTen['FIRST_NAME'], // tách ra từ họ tên lấy chữ cuối 
            "LAST_NAME" => $arrayHoVaTen['LAST_NAME'], // tách ra từ họ tên lấy phần còn 
            "GENDER_ID" => $this->params->request->genderId, // id giới tính 
            "DOB" => $this->params->request->dob, // ngày sinh
            // "IS_HAS_NOT_DAY_DOB" => 0, 
            "NATIONAL_CODE" => $dataNational->national_code ?? "", // quốc gia 
            "NATIONAL_NAME" => $dataNational->national_name ?? "", // quốc gia 
            "ETHNIC_CODE" => $dataEthnic->ethnic_code ?? "", // dân tộc 
            "ETHNIC_NAME" => $dataEthnic->ethnic_name ?? "", // dân tộc
            "PROVINCE_CODE" => $dataProvince->province_code ?? "", // tỉnh 
            "PROVINCE_NAME" => $dataProvince->province_name ?? "", // tỉnh 
            "COMMUNE_CODE" => $dataCommune->commune_code ?? "", // xã 
            "COMMUNE_NAME" => $dataCommune->commune_name ?? "", //xã
            "ADDRESS" => $this->params->request->address ?? "", // địa chỉ phần liên hệ
            "HT_ADDRESS" => $this->params->request->htAddress ?? null, // địa chỉ hiện tại
            "PHONE" => $this->params->request->phone ?? null, // số điện thoại phần liên hệ
            "RELATIVE_TYPE" => $this->params->request->relativeType ?? "", // quan hệ phần người thân 
            "RELATIVE_NAME" => $this->params->request->relativeName ?? "", // người nhà 
            "RELATIVE_ADDRESS" => $this->params->request->relativeAddress ?? "", // địa chỉ phần người thân
            // "RELATIVE_CMND_NUMBER" => $this->params->request->relativeCmndNumber, // cmnd phần người thân, 9 số
            // "RELATIVE_MOBILE" => $this->params->request->phone, // lấy điện thoại phần liên hệ 
            "RELATIVE_PHONE" => $this->params->request->relativePhone ?? "", // điện thoại phần người thân
            "CAREER_CODE" => $dataCareer->career_code ?? "", // nghề nghiệp
            "CAREER_NAME" => $dataCareer->career_name ?? "", // nghề nghiệp
            "CAREER_ID" => $this->params->request->careerId, // id nghề nghiệp
            // "WORK_PLACE_ID" => $this->params->request->workPlaceId, // id nơi làm việc hoặc ép số workPlaceId nếu tìm được thông tin bệnh nhân
            "WORK_PLACE" => $this->params->request->workPlace ?? null, // nơi làm việc nếu tự gõ hoặc workPlace nếu tìm được thông tin bệnh nhân
            "BRANCH_ID" => $this->branchIdRoomIdMacDinh, // id chi nhánh, lấy từ tài khoản đang đăng nhập
            // "BLOOD_ABO_CODE" => $dataBloodAbo->blood_abo_code ?? "", // "" hoặc code nhóm máu phần mở rộng
            // "BLOOD_RH_CODE" => $dataBloodRh->blood_rh_code ?? "", // "" hoặc code Rh phần mở rộng
            "CCCD_NUMBER" => $this->params->request->cccdNumber ?? null, // số cccd phần thông tin khác, 12 số k được trùng
            "CCCD_DATE" => $this->params->request->cccdDate ?? null, // ngày cấp cccd phần thông tin khác
            "CCCD_PLACE" => $this->params->request->cccdPlace ?? null, // nơi cấp cccd phần thông tin khác
            "MOTHER_NAME" => $this->params->request->motherName ?? "", // tên mẹ
            "FATHER_NAME" => $this->params->request->fatherName ?? "", // tên bố
            "TAX_CODE" => $this->params->request->taxCode ?? "", // null hoặc mã số thuế phần mở rộng
            // "PATIENT_CLASSIFY_ID" => $this->params->request->patientClassifyId, // phân loại bệnh nhân
            // "IS_TUBERCULOSIS" => $this->params->request->isTuberculosis ?? 0, // Bệnh nhân lao
            // "IS_HIV" => $this->params->request->isHiv ?? 0, // bệnh nhân HIV/AIDS check = 1
        ];

        return $data;
    }
    private function getHisPatient()
    {
        try {
            $hisPatient = [];
            $hisPatientArrayRequest = $this->getHisPatientArrayRequest(); // Mảng body phần hisPatient từ request
            if (!($this->params->request->patientId == 0)) {
                // Nếu có thông tin Patient thì lấy lại thông tin đó
                $dataPatient = $this->patient
                    ->where('is_delete', 0)
                    ->find($this->params->request->patientId)
                    ->getAttributes(); // lấy dạng snake_case
                if (empty($dataPatient)) {
                    throw new \Exception('Không tìm thấy thông tin bệnh nhân hoặc thông tin đã bị xóa.');
                }
                $dataPatient = array_change_key_case($dataPatient, CASE_UPPER); // Định dạng lại in hoa
                $merged = array_merge($dataPatient, $hisPatientArrayRequest); // Ghi đè các trường của thông tin bệnh nhân trong DB bằng các trường mà FE gửi lên
                $hisPatient = $merged;
            } else {
                // Nếu tạo mới thì chỉ gửi mảng từ request
                $hisPatient = $hisPatientArrayRequest;
            }
            return $hisPatient;
        } catch (\Throwable $e) {
            throw new \Exception('Có lỗi khi tạo hoặc lấy thông tin bệnh nhân cho việc đăng ký khám.');
        }
    }
    private function getHisTreatment()
    {
        try {
            $dataHospitalizeReason = $this->getCacheHospitalizeReason();
            $hisTreatment = [
                "ID" => 0,
                "PATIENT_ID" => $this->params->request->patientId ?? 0,
                // "HOSPITALIZATION_REASON" => $this->params->request->hospitalizationReason ?? "", // Lý do vào viện
                // "OWE_TYPE_ID" => $this->params->request->oweTypeId, // nợ viện phí phần yêu cầu khác
                // "OWE_MODIFY_TIME" => $this->params->request->oweTypeId ? $this->params->request->thoiGianYeuCauKhac : null, // Thời gian phần yêu cầu khác, chọn nợ viện phí mới có thời gian
                // "IS_EMERGENCY" => $this->params->request->isEmergency, // cấp cứu phần yêu cầu khác thì = 1
                // "EMERGENCY_WTIME_ID" => $this->params->request->isEmergency ? $this->params->request->emergencyWtimeId : null, // id thời gian đau, chọn cấp cứu mới có
                // "TREATMENT_ORDER" => $this->params->request->treatmentOrder, // số thứ tự hồ sơ
                // "OTHER_PAY_SOURCE_ID" => $this->params->request->otherPaySourceId, // id nguồn khác chi trả
                // "HOSPITALIZE_REASON_CODE" => $dataHospitalizeReason->hospitalize_reason_code ?? "", // lý do vào nội trú 
                // "HOSPITALIZE_REASON_NAME" => $dataHospitalizeReason->hospitalize_reason_name ?? "", // lý do vào nội trú
            ];
            return $hisTreatment;
        } catch (\Throwable $e) {
            throw new \Exception('Có lỗi khi tạo hoặc lấy thông tin điều trị cho việc đăng ký khám.');
        }
    }
    private function getHisPatientTypeAlter()
    {
        try {
            $hisPatientTypeAlter = [
                "ID" => 0,
                "TREATMENT_TYPE_ID" => $this->treatmentTypeIdMacDinh, // diện điều trị phần yêu cầu khác, mặc định khám
                "PATIENT_TYPE_ID" => $this->patientTypeIdMacDinh, // đối tượng thanh toán - mặc định viện phí
                // "GUARANTEE_LOGINNAME" => $this->params->request->guaranteeLoginname ?? "", // người bảo lãnh
                // "GUARANTEE_USERNAME" => $this->params->request->guaranteeUsername ?? "", // người bảo lãnh
                // "GUARANTEE_REASON" => $this->params->request->guaranteeReason ?? "", // lý do bảo lãnh
            ];
            return $hisPatientTypeAlter;
        } catch (\Throwable $e) {
            throw new \Exception('Có lỗi khi tạo thông tin bảng kê cho việc đăng ký khám.');
        }
    }
    private function checkHasServiceRoom($item)
    {
        try {
            return $this->serviceRoom
                ->where('service_id', $item['serviceId'] ?? 0)
                ->where('room_id', $item['roomId'] ?? 0)
                ->where('is_active', 1)
                ->where('is_delete', 0)
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }
    private function getServiceReqDetails()
    {
        $serviceReqDetails = [];
        foreach ($this->params->request->serviceReqDetails ?? [] as $key => $item) {
            $hasServiceRoom = $this->checkHasServiceRoom($item);
            if (!$hasServiceRoom) {
                throw new \Exception('Phòng và dịch vụ công khám đã chọn không khớp.');
            }
            $arrItem = [
                "DummyId" => null,
                "AttachedDummyId" => null,
                "PatientTypeId" => $this->patientTypeIdMacDinh, // id đối tượng thanh toán, mặc định viện phí
                "ServiceId" => $item['serviceId'], // id dịch vụ khám đã chọn 
                "ParentId" => null,
                "RoomId" => $item['roomId'], // roomId phòng khám
                "EkipId" => null,
                "Amount" => 1, // số lượng mặc định 1
                "IsExpend" => null,
                "IsOutParentFee" => null,
                "ShareCount" => null,
                "IsNoHeinDifference" => false,
                "InstructionNote" => null,
                "SereServId" => null,
                "PrimaryPatientTypeId" => null,
                "UserPrice" => null,
                "UserPackagePrice" => null,
                "PackageId" => null,
                "AssignedExecuteLoginName" => null,
                "AssignedExecuteUserName" => null,
                "ServiceConditionId" => null,
                "OtherPaySourceId" => null,
                "NumOrderBlockId" => null,
                "NumOrderIssueId" => null,
                "NumOrder" => null,
                "BedStartTime" => null,
                "BedFinishTime" => null,
                "BedId" => null,
                "IsNotUseBhyt" => false,
                "SampleTypeCode" => null,
                "AssignNumOrder" => null,
                "MultipleExecute" => null,
                "EkipInfos" => null
            ];
            $serviceReqDetails[] = $arrItem; // thêm vào mảng dịch vụ công khám
        }
        return $serviceReqDetails;
    }
    private function getRawBodyDangKyKham()
    {
        // đã bắt lỗi trong getHisPatient getHisTreatment getHisPatientTypeAlter
        $hisPatient = $this->getHisPatient();
        $hisTreatment = $this->getHisTreatment();
        $hisPatientTypeAlter = $this->getHisPatientTypeAlter();
        $serviceReqDetails = $this->getServiceReqDetails();
        try {
            $rawBody = [
                "CommonParam" => [
                    "Messages" => [],
                    "BugCodes" => [],
                    "MessageCodes" => [],
                    "Start" => null,
                    "Limit" => null,
                    "Count" => null,
                    "ModuleCode" => null,
                    "LanguageCode" => "VI",
                    "Now" => 0,
                    "HasException" => false
                ],
                "ApiData" => [
                    "HisPatientProfile" => [
                        "HisPatient" => $hisPatient, // mảng hisPatient
                        "HisTreatment" => $hisTreatment, // mảng hisTreatment
                        "HisPatientTypeAlter" => $hisPatientTypeAlter, // mảng hisPatientTyeAlter
                        "DepartmentId" => $this->departmentIdRoomIdMacDinh, // id khoa của phòng đang chọn hiện tại
                        "CardCode" => null,
                        "CardServiceCode" => null,
                        "BankCardCode" => null,
                        "TreatmentTime" => $this->params->request->thoiGianYeuCauKhac, // thời gian phần yêu cầu khác
                        "ProvinceCode" => "91", // tỉnh
                        "DistrictCode" => null,
                        "RequestRoomId" => 476, // roomId phòng đang chọn hiện tại
                        // "IsChronic" => $this->params->request->isChronic ?? false, // Mãn tính phần yêu cầu khác
                        "ImgBhytData" => null,
                        "ImgAvatarData" => null,
                        "ImgCmndBeforeData" => null,
                        "ImgCmndAfterData" => null,
                        "ImgTransferInData" => null
                    ],
                    "AccountBookId" => null,
                    "PayFormId" => null,
                    "TransNumOrder" => null,
                    "CashierLoginName" => null,
                    "CashierUserName" => null,
                    "CashierWorkingRoomId" => null,
                    "IsAutoCreateBillForNonBhyt" => false,
                    "IsAutoCreateDepositForNonBhyt" => false,
                    "IsUsingEpayment" => false,
                    "InstructionTime" => $this->params->request->thoiGianYeuCauKhac, // thời gian phần yêu cầu khác
                    "ServiceReqDetails" =>  $serviceReqDetails, // Lặp qua từng hàng phòng khám đang chọn (là phần mảng ids để gọi api lấy dịch vụ khám) => mỗi phần tử ở dưới tương ứng với  1 phòng

                    "ExecuteGroupId" => null,
                    // "Priority" => $this->params->request->priority ?? 0, // ưu tiên phần yêu cầu khác
                    "PriorityTypeId" => null, // Id loại ưu tiên
                    // "IsNotRequireFee" => $this->params->request->isNotRequireFee ?? null, // check thu sau = 1
                    "IsNoExecute" => false,
                    "IsEmergency" => $this->params->request->isEmergency ?? false, // cấp cứu
                    "IsInformResultBySms" => false,
                    "ManualRequestRoomId" => false,
                    "SessionCode" => null,
                    "InstructionTimes" => null,
                    "TrackingInfos" => null,
                    // "Note" => $this->params->request->note ?? "", // ghi chú phần yêu cầu khác
                    "IsExamOnline" => false,
                    "UseTimes" => null,
                    "NumOrder" => null,
                    "Id" => null,
                    "ParentServiceReqId" => null,
                    "IcdText" => null,
                    "IcdCode" => null,
                    "IcdName" => null,
                    "IcdCauseCode" => null,
                    "TraditionalIcdCode" => null,
                    "TraditionalIcdName" => null,
                    "TraditionalIcdSubCode" => null,
                    "TraditionalIcdText" => null,
                    "IcdCauseName" => null,
                    "IcdSubCode" => null,
                    "ProvisionalDiagnosis" => null,
                    "Description" => null,
                    "TreatmentId" => 0,
                    "TrackingId" => null,
                    "RequestRoomId" => $this->roomIdMacDinh, // roomId phòng đang chọn
                    "RequestLoginName" => $this->taiKhoanMacDinh['Data']['User']['LoginName'], // người đang đăng nhập
                    "RequestUserName" => $this->taiKhoanMacDinh['Data']['User']['UserName'], // người đang dăng nhập
                    "ConsultantLoginName" => null,
                    "ConsultantUserName" => null,
                    "KidneyShift" => null,
                    "MachineId" => null,
                    "ExpMestTemplateId" => null,
                    "IsKidney" => false,
                    "AssignTimeTo" => null
                ]
            ];
            return $rawBody;
        } catch (\Throwable $e) {
            throw new \Exception('Có lỗi khi tạo thông tin cho việc đăng ký khám.');
        }
    }
    private function tachHoVaTen()
    {
        $data = tachHoTen($this->params->request->hoVaTen);
        if (isset($data['FIRST_NAME']) && mb_strlen($data['FIRST_NAME']) > 30) {
            throw new \Exception('Tên vượt quá 30 ký tự.');
        }
        if (isset($data['LAST_NAME']) && mb_strlen($data['LAST_NAME']) > 70) {
            throw new \Exception('Họ và chữ lót vượt quá 70 ký tự.');
        }
        return $data;
    }
    public function getCacheNational()
    {
        $cacheKey = 'guest_national_id_' . $this->params->request->nationalId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->national->find($this->params->request->nationalId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }

    public function getCacheEthnic()
    {
        $cacheKey = 'guest_ethnic_id_' . $this->params->request->ethnicId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->ethnic->find($this->params->request->ethnicId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function getCacheProvince()
    {
        $cacheKey = 'guest_province_id_' . $this->params->request->provinceId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->province->find($this->params->request->provinceId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function getCacheCommune()
    {
        $cacheKey = 'guest_commune_id_' . $this->params->request->communeId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->commune->find($this->params->request->communeId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function getCacheCareer()
    {
        $cacheKey = 'guest_career_id_' . $this->params->request->careerId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->career->find($this->params->request->careerId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function getCacheBloodAbo()
    {
        $cacheKey = 'guest_blood_abo_id' . $this->params->request->bloodAboId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->bloodAbo->find($this->params->request->bloodAboId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function getCacheBloodRh()
    {
        $cacheKey = 'guest_blood_rh_id' . $this->params->request->bloodRhId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->bloodRh->find($this->params->request->bloodRhId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function getCacheHospitalizeReason()
    {
        $cacheKey = 'guest_hospitalize_reason_id' . $this->params->request->hospitalizeReasonId;
        $data =  Cache::remember($cacheKey, $this->timeCacheMacDinh, function () {
            $data = $this->hospitalizeReason->find($this->params->request->hospitalizeReasonId);
            return $data;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySetting, [$cacheKey]);
        Redis::connection('cache')->sadd($this->cacheKeyGuest, [$cacheKey]);
        return $data;
    }
    public function handleDangKyKham()
    {
        // Lấy tài khoản mặc định
        $this->layTaiKhoanMacDinhChoDangKyKham();
        // Đăng ký phiên làm việc
        $this->dangKyPhienLamViecChoPhongMacDinh();
        // check dưới 6 tuổi nếu k có đủ thông tin người thân thì ném ra lỗi
        $this->validate();
        // lấy rawBody api ExamRegister
        $rawBody = $this->getRawBodyDangKyKham();
        // Gọi api đăng ký khám / api đăng ký thông tin
        return $this->callApiMos($rawBody);
    }
}
