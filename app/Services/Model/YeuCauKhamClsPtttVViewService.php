<?php

namespace App\Services\Model;

use App\DTOs\YeuCauKhamClsPtttVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\YeuCauKhamClsPtttVView\InsertYeuCauKhamClsPtttVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Models\HIS\Treatment;
use App\Repositories\AllergenicRepository;
use App\Repositories\AppointmentServRepository;
use App\Repositories\MedicalCaseCoverListVViewRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PatientTypeAlterRepository;
use App\Repositories\SereServListVViewRepository;
use App\Repositories\ServiceRoomRepository;
use App\Repositories\TreatmentFeeDetailVViewRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\YeuCauKhamClsPtttVViewRepository;
use Illuminate\Support\Facades\DB;

class YeuCauKhamClsPtttVViewService
{
    protected $yeuCauKhamClsPtttVViewRepository;
    protected $serviceRoomRepository;
    protected $sereServListVViewRepository;
    protected $medicalCaseCoverListVViewRepository;
    protected $allergenicRepository;
    protected $patientRepository;
    protected $patientTypeAlterRepository;
    protected $appointmentServRepository;
    protected $treatment;
    protected $treatmentFeeDetailVViewRepository;
    protected $params;
    public function __construct(
        YeuCauKhamClsPtttVViewRepository $yeuCauKhamClsPtttVViewRepository,
        ServiceRoomRepository $serviceRoomRepository,
        SereServListVViewRepository $sereServListVViewRepository,
        MedicalCaseCoverListVViewRepository $medicalCaseCoverListVViewRepository,
        AllergenicRepository $allergenicRepository,
        PatientRepository $patientRepository,
        PatientTypeAlterRepository $patientTypeAlterRepository,
        AppointmentServRepository $appointmentServRepository,
        Treatment $treatment,
        TreatmentFeeDetailVViewRepository $treatmentFeeDetailVViewRepository,
    ) {
        $this->yeuCauKhamClsPtttVViewRepository = $yeuCauKhamClsPtttVViewRepository;
        $this->serviceRoomRepository = $serviceRoomRepository;
        $this->sereServListVViewRepository = $sereServListVViewRepository;
        $this->medicalCaseCoverListVViewRepository = $medicalCaseCoverListVViewRepository;
        $this->allergenicRepository = $allergenicRepository;
        $this->patientRepository = $patientRepository;
        $this->patientTypeAlterRepository = $patientTypeAlterRepository;
        $this->appointmentServRepository = $appointmentServRepository;
        $this->treatment = $treatment;
        $this->treatmentFeeDetailVViewRepository = $treatmentFeeDetailVViewRepository;
    }
    public function withParams(YeuCauKhamClsPtttVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoins();
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsNoExecuteFilter($data);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyKskContractIdFilter($data, $this->params->kskContractId);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyServiceReqCodeFilter($data, $this->params->serviceReqCode);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyBedCodeFilter($data, $this->params->bedCode);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiFilter($data, $this->params->trangThai);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiVienPhiFilter($data, $this->params->trangThaiVienPhi);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiKeThuocFilter($data, $this->params->trangThaiKeThuoc);
            $count = $data->count();
            $orderBy = [
                'intruction_date' => 'desc',
                'priority' => 'desc',
                'num_order' => 'asc'
            ];
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyOrdering($data, $orderBy, []);
            $data = $this->yeuCauKhamClsPtttVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['yeu_cau_kham_cls_pttt_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoins();
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyKskContractIdFilter($data, $this->params->kskContractId);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyServiceReqCodeFilter($data, $this->params->serviceReqCode);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyBedCodeFilter($data, $this->params->bedCode);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiFilter($data, $this->params->trangThai);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiVienPhiFilter($data, $this->params->trangThaiVienPhi);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiKeThuocFilter($data, $this->params->trangThaiKeThuoc);
        $count = $data->count();
        $orderBy = [
            'intruction_date' => 'desc',
            'priority' => 'desc',
            'num_order' => 'asc'
        ];
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyOrdering($data, $orderBy, []);
        $data = $this->yeuCauKhamClsPtttVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoinsDataKhamBenh();
        $data = $data->where('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.id', $id);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsNoExecuteFilter($data);
        $data = $data->first();
        return $data;
    }
    private function getDuLieu($serviceReqCode)
    {
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoinsLayDuLieu();
        $data = $data->where('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.service_req_code', $serviceReqCode);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsNoExecuteFilter($data);
        $data = $data->first();
        return $data;
    }
    private function getDataLichSuKham($patientId, $treatmentId)
    {
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoinsLichSuKham();
        $data = $data
        ->whereNotIn('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.treatment_id', [$treatmentId])
        ->where('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.patient_id', $patientId)
        ->where('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.service_req_type_code', 'KH')
        ->whereNull('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.parent_id');
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsNoExecuteFilter($data);
        $orderBy = [
            'intruction_date' => 'desc',
            'priority' => 'desc',
            'num_order' => 'asc'
        ];
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyOrdering($data, $orderBy, []);
        $data = $data->get();
        return $data;
    }
    private function getDataXuTriKham($patientId)
    {
        $data = $this->patientRepository->applyJoinsXutriKham()
        ->where('his_patient.id', $patientId)
        ->where('his_patient.is_delete', 0);
        $data = $data->first();
        return $data;
    }
    private function getDanhSachDichVuKhamDaChon($treatmentId)
    {
        $data = $this->appointmentServRepository->getByTreatmentId($treatmentId);
        return $data;
    }
    private function getPrimaryPatientType($treatmentId, $heinCardNumber)
    {
        $data = $this->patientTypeAlterRepository->applyJoinsXutriKham()
        ->where('his_patient_type_alter.treatment_id', $treatmentId)
        ->where('his_patient_type_alter.hein_card_number', $heinCardNumber)
        ->where('his_patient_type_alter.is_delete', 0);
        $orderBy = [
            'modify_time' => 'desc',
        ];
        $data = $this->patientTypeAlterRepository->applyOrdering($data, $orderBy, []);
        $data = $data->first();
        return $data;
    }
    private function getDataDotKhamHienTai($treatmentId)
    {
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoinsDotKhamHienTai();
        $data = $data
        ->whereIn('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.treatment_id', [$treatmentId])
        ->where('XA_V_HIS_YEU_CAU_KHAM_CLS_PTTT.service_req_type_code', 'KH');
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsNoExecuteFilter($data);
        $orderBy = [
            'intruction_time' => 'desc',
        ];
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyOrdering($data, $orderBy, []);
        $data = $data->get();
        return $data;
    }
    private function getDataDiUngThuoc($patientId)
    {
        $data = $this->allergenicRepository->applyJoinsDataDiUngThuoc();
        $data = $this->allergenicRepository->applyIsActiveFilter($data, 1);
        $data = $this->allergenicRepository->applyIsDeleteFilter($data, 0);
        $data = $this->allergenicRepository->applyPatientIdFilter($data, $patientId);
        $orderBy = [
            'modify_time' => 'desc',
        ];
        $data = $this->allergenicRepository->applyOrdering($data, $orderBy, []);
        $data = $data->get();
        return $data;
    }
    private function getDataTreatment($treatmentId)
    {
        $data = $this->treatment
        ->leftJoin('his_branch','his_branch.id', '=', 'his_treatment.branch_id')
        ->leftJoin('his_patient','his_patient.id', '=', 'his_treatment.patient_id')
        ->select([
            'his_treatment.in_code',
            'his_branch.branch_code',
            'his_branch.branch_name',
            'his_treatment.in_time',
            'his_treatment.tdl_patient_relative_name',
            'his_treatment.tdl_patient_relative_phone',
            'his_treatment.tdl_patient_relative_address',
            'his_patient.career_id',
            'his_patient.career_code',
            'his_patient.career_name',
            ])
        ->find($treatmentId);
        return $data;
    }
    public function getDataVienPhi($duLieu){
        $data = $this->treatmentFeeDetailVViewRepository->getDataVienPhi($duLieu->treatment_id);
        return $data;
    }
    private function getDataKhamBenh($treatmentId)
    {
        $data = $this->medicalCaseCoverListVViewRepository->applyJoinsYeuCauKhamClsPttt()
            ->where('id', $treatmentId);
        $data = $this->medicalCaseCoverListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $data->first();
        $data = $this->medicalCaseCoverListVViewRepository->themMucHuongBHYT($data);
        return $data;
    }
    private function getDataDanhMucDichVuYeuCauCuaPhong($executeRoomId = -1)
    {
        $data = $this->serviceRoomRepository->applyJoins();
        $data = $this->serviceRoomRepository->applyIsActiveFilter($data, 1);
        $data = $this->serviceRoomRepository->applyRoomIdFilter($data, $executeRoomId);
        $data = $this->serviceRoomRepository->fetchData($data, true, $this->params->start, $this->params->limit);
        return $data;
    }
    private function getDataDanhSachDichVuChiDinhCuaLanDieuTri($treatmentId = -1, $serviceIds, $tab = 'tatCa', $serviceReqId)
    {
        $data = $this->sereServListVViewRepository->applyJoinsDichVuChiDinh();
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        switch ($tab) {
            case 'tatCa':
                $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
                break;
            case 'tatCaKhongBaoGomDichVuNoiTru':
                // bỏ các Y lệnh có treatmentTypeCode là 03
                $data = $this->sereServListVViewRepository->applyNotInDichVuNoiTruFilter($data);
                $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
                break;
            case 'cacChiDinhDangDuocChon':
                // lấy các service của các y lệnh có parentId là y lệnh đang chọn
                $data = $this->sereServListVViewRepository->applyParentServiceReqIdFilter($data, $serviceReqId);
                break;
            default:
                $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
                break;
        }
        $data = $this->sereServListVViewRepository->applyOrdering($data, ['service_code' => 'asc'], []);
        $data = $this->sereServListVViewRepository->fetchData($data, true, $this->params->start, $this->params->limit);
        // Group theo field
        $groupBy = [
            'ServiceTypeName',
            "ServiceReqCode",
        ];
        $data = $this->sereServListVViewRepository->applyGroupByFieldYeuCauClsPttt($data, $groupBy);
        return $data;
    }
    private function getDataDanhSachDichVuYeuCauCuaLanDieuTri($treatmentId = -1, $serviceIds, $serviceReqId)
    {
        $data = $this->sereServListVViewRepository->applyJoinsDichVuYeuCau();
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
        $data = $this->sereServListVViewRepository->applyServiceIdsFilter($data, $serviceIds);
        $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $serviceReqId);
        $data = $this->sereServListVViewRepository->applyOrdering($data, ['service_code' => 'asc'], []);
        $data = $this->sereServListVViewRepository->fetchData($data, true, $this->params->start, $this->params->limit);
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['yeu_cau_kham_cls_pttt_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $dataYeuCau = $this->getDataById($id);
            if(empty($dataYeuCau)) return null;
            $data = [];
            $dataThongTinKhamBenh = [];
            $dataIdDanhMucDichVuYeuCauCuaPhong = [];
            $dataDanhSachDichVuChiDinhCuaLanDieuTri = [];
            $dataDanhSachDichVuYeuCauCuaLanDieuTri = [];
            $dataKhamBenh = null;
            if ($dataYeuCau) {
                $dataThongTinKhamBenh = $this->getDataKhamBenh($dataYeuCau->treatment_id);
                $dataIdDanhMucDichVuYeuCauCuaPhong = $this->getDataDanhMucDichVuYeuCauCuaPhong($dataYeuCau->execute_room_id)->pluck('service_id');
                $dataDanhSachDichVuChiDinhCuaLanDieuTri = $this->getDataDanhSachDichVuChiDinhCuaLanDieuTri($dataYeuCau->treatment_id, $dataIdDanhMucDichVuYeuCauCuaPhong, $this->params->tab, $id);
                $dataDanhSachDichVuYeuCauCuaLanDieuTri = $this->getDataDanhSachDichVuYeuCauCuaLanDieuTri($dataYeuCau->treatment_id, $dataIdDanhMucDichVuYeuCauCuaPhong, $id);
                $dataKhamBenh = $dataYeuCau;
            }

            $loaiKetThucKhamMap = [
                1 => 'Khám thêm',
                2 => 'Nhập viện',
                3 => 'Kết thúc điều trị',
                4 => 'Kết thúc khám',
            ];

            $data['thongTinKhamBenh'] = $dataThongTinKhamBenh;
            $data['thongTinKhamBenh']['note'] = $dataYeuCau?->note ?? null;
            $data['thongTinKhamBenh']['examEndType'] = $dataYeuCau?->exam_end_type ?? null;
            $data['thongTinKhamBenh']['thongTinXuTriGanNhat'] = $loaiKetThucKhamMap[$dataYeuCau?->exam_end_type] ?? null;

            $data['dichVuYeuCau'] = $dataDanhSachDichVuYeuCauCuaLanDieuTri;
            $data['dichVuChiDinh'] = $dataDanhSachDichVuChiDinhCuaLanDieuTri;
            $data['khamBenh'] = $dataKhamBenh;


            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['yeu_cau_kham_cls_pttt_v_view'], $e);
        }
    }
    public function handleDataBaseLayDuLieu($serviceReqCode)
    {
        try {
            $duLieu = $this->getDuLieu($serviceReqCode);
            $data = [];
            $dataLichSuKham = [];
            $dataXuTriKham = [];
            $dataDotKhamHienTai = [];
            $dataDiUngThuoc = [];
            $thongTinVienPhi = [];
            if($duLieu){
                $duLieu['xepLoaiBMI'] = xepLoaiBMI($duLieu->virBmi);
                $dataLichSuKham = $this->getDataLichSuKham($duLieu->patient_id, $duLieu->treatment_id);
                $dataXuTriKham = $this->getDataXuTriKham($duLieu->patient_id)->toArray();
                $dataXuTriKham['isMainExam'] = $duLieu->is_main_exam;
                $dataXuTriKham['isAutoFinished'] = $duLieu->is_auto_finished;
                $dataXuTriKham['tdlHeinCardNumber'] = $duLieu->tdl_hein_card_number;
                $dataXuTriKham['maBHXH'] = $duLieu->tdl_hein_card_number ? substr($duLieu->tdl_hein_card_number, -10) : null;
                $dataXuTriKham['danhSachDichVuKhamDaChon'] = $this->getDanhSachDichVuKhamDaChon($duLieu->treatment_id);

                $dataTreatment = $this->getDataTreatment($duLieu->treatment_id);
                $dataXuTriKham += $dataTreatment ? $dataTreatment->toArray(): [];

                $dataPrimaryPatientType = $this->getPrimaryPatientType($duLieu->treatment_id, $duLieu->tdl_hein_card_number);
                $dataXuTriKham += $dataPrimaryPatientType ? $dataPrimaryPatientType->toArray() : [];
                $dataDotKhamHienTai = $this->getDataDotKhamHienTai($duLieu->treatment_id);
                $dataDiUngThuoc = $this->getDataDiUngThuoc($duLieu->patient_id);
                // $thongTinVienPhi = $this->getDataVienPhi($duLieu);
            }
            $data['khamBenh'] = $duLieu;
            $data['lichSuKham'] = $dataLichSuKham;
            $data['xuTriKham'] = $dataXuTriKham;
            $data['dotKhamHienTai'] = $dataDotKhamHienTai;
            $data['diUngThuoc'] = $dataDiUngThuoc;
            // $data['khamBenh']['thongTinVienPhi'] = $thongTinVienPhi;

            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['yeu_cau_kham_cls_pttt_v_view'], $e);
        }
    }
}
