<?php

namespace App\Services\Model;

use App\DTOs\YeuCauKhamClsPtttVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\YeuCauKhamClsPtttVView\InsertYeuCauKhamClsPtttVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\MedicalCaseCoverListVViewRepository;
use App\Repositories\SereServListVViewRepository;
use App\Repositories\ServiceRoomRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\YeuCauKhamClsPtttVViewRepository;
use Illuminate\Support\Facades\DB;

class YeuCauKhamClsPtttVViewService
{
    protected $yeuCauKhamClsPtttVViewRepository;
    protected $serviceRoomRepository;
    protected $sereServListVViewRepository;
    protected $medicalCaseCoverListVViewRepository;
    protected $params;
    public function __construct(
        YeuCauKhamClsPtttVViewRepository $yeuCauKhamClsPtttVViewRepository,
        ServiceRoomRepository $serviceRoomRepository,
        SereServListVViewRepository $sereServListVViewRepository,
        MedicalCaseCoverListVViewRepository $medicalCaseCoverListVViewRepository,
    ) {
        $this->yeuCauKhamClsPtttVViewRepository = $yeuCauKhamClsPtttVViewRepository;
        $this->serviceRoomRepository = $serviceRoomRepository;
        $this->sereServListVViewRepository = $sereServListVViewRepository;
        $this->medicalCaseCoverListVViewRepository = $medicalCaseCoverListVViewRepository;
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
        $data = $data->first();
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
                $data = $this->sereServListVViewRepository->applyNotInDichVuNoiTruFilter($data);
                $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
                break;
            case 'cacChiDinhDangDuocChon':
                $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $serviceReqId);
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
    private function getDataDanhSachDichVuYeuCauCuaLanDieuTri($treatmentId = -1, $serviceIds)
    {
        $data = $this->sereServListVViewRepository->applyJoinsDichVuYeuCau();
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
        $data = $this->sereServListVViewRepository->applyServiceIdsFilter($data, $serviceIds);
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
                $dataDanhSachDichVuYeuCauCuaLanDieuTri = $this->getDataDanhSachDichVuYeuCauCuaLanDieuTri($dataYeuCau->treatment_id, $dataIdDanhMucDichVuYeuCauCuaPhong);
                $dataKhamBenh = $dataYeuCau;
            }

            $loaiKetThucKhamMap = [
                1 => 'Khám thêm',
                2 => 'Nhập viện',
                3 => 'Kết thúc điều trị',
                4 => 'Kết thúc khám',
            ];

            $data['thongTinKhamBenh'] = $dataThongTinKhamBenh;
            $data['thongTinKhamBenh']['note'] = $dataYeuCau->note;
            $data['thongTinKhamBenh']['examEndType'] = $dataYeuCau->exam_end_type;
            $data['thongTinKhamBenh']['thongTinXuTriGanNhat'] = $loaiKetThucKhamMap[$dataYeuCau->exam_end_type] ?? '';

            $data['dichVuYeuCau'] = $dataDanhSachDichVuYeuCauCuaLanDieuTri;
            $data['dichVuChiDinh'] = $dataDanhSachDichVuChiDinhCuaLanDieuTri;
            $data['khamBenh'] = $dataKhamBenh;


            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['yeu_cau_kham_cls_pttt_v_view'], $e);
        }
    }
}
