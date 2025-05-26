<?php

namespace App\Services\Model;

use App\DTOs\BangKeVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BangKeVView\InsertBangKeVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Models\HIS\SereServ;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BangKeVViewRepository;

class BangKeVViewService
{
    protected $bangKeVViewRepository;
    protected $sereServ;
    protected $params;
    public function __construct(
        BangKeVViewRepository $bangKeVViewRepository,
        SereServ $sereServ,
    ) {
        $this->bangKeVViewRepository = $bangKeVViewRepository;
        $this->sereServ = $sereServ;
    }
    public function withParams(BangKeVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bangKeVViewRepository->applyJoins();
            $data = $this->bangKeVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->bangKeVViewRepository->applyAmountGreaterThan0Filter($data, $this->params->amountGreaterThan0);
            $count = $data->count();
            $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            $data = $this->bangKeVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyAmountGreaterThan0Filter($data, $this->params->amountGreaterThan0);
        $count = $data->count();
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $data = $this->bangKeVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNgoaiTruHaoPhi()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNgoaiTruHaoPhiFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNgoaiTruBHYTHaoPhi()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNgoaiTruBHYTHaoPhiFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNgoaiTruVienPhiTPTB()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNgoaiTruVienPhiTPTBFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->customizeBangKeNgoaiTruVienPhiTPTB($data); // Thêm vào 1 bản ghi nếu bản ghi có đủ patient_type_id và primary_patient_type_id
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNgoaiTruBHYTTheoKhoa6556QDBYT()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->addJsonPatientTypeAlter($data);
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNgoaiTruBHYTTheoKhoa6556QDBYTFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinCardNumber",
            "requestDepartmentName",
            "requestRoomName",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",

        ];
        $data = $this->bangKeVViewRepository->customizeBangKeNgoaiTruBHYTTheoKhoa6556QDBYT($data);
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNgoaiTruVienPhiTheoKhoa6556QDBYT()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNgoaiTruVienPhiTheoKhoaFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "requestDepartmentName",
            "requestRoomName",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->customizeBangKeNgoaiTruVienPhiTheoKhoa6556QDBYT($data);
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNoiTruHaoPhi()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNoiTruHaoPhiFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",

        ];
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNoiTruVienPhiTPTB()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNoiTruVienPhiTPTBFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->customizeBangKeNoiTruVienPhiTPTB($data); // Thêm vào 1 bản ghi nếu bản ghi có đủ patient_type_id và primary_patient_type_id
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNoiTruBHYTTheoKhoa6556QDBYT()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->addJsonPatientTypeAlter($data);
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNoiTruBHYTTheoKhoa6556QDBYTFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinCardNumber",
            "requestDepartmentName",
            "requestRoomName",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->customizeBangKeNoiTruBHYTTheoKhoa6556QDBYT($data);
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeNoiTruVienPhiTheoKhoa6556QDBYT()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeNoiTruVienPhiTheoKhoa6556QDBYTFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "requestDepartmentName",
            "requestRoomName",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",
        ];
        $data = $this->bangKeVViewRepository->customizeBangKeNoiTruVienPhiTheoKhoa6556QDBYT($data); // Lặp qua để đổi các requestRoom của thuốc và vật tư thành Buồng điều trị, đổi các serviceTypeName
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getBangKeTongHop6556KhoaPhongThanhToan()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyBangKeTongHop6556KhoaPhongThanhToanFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinCardNumber",
            "isExpend",
            "requestDepartmentName",
            "requestRoomName",
            "heinServiceTypeName",
            "tdlServiceName",
            "patientTypeName",

        ];
        $data = $this->bangKeVViewRepository->customizeBangKeTongHop6556KhoaPhongThanhToan($data); 
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getTongHopNgoaiTruVienPhiHaoPhi()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->bangKeVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->bangKeVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->bangKeVViewRepository->applyTongHopNgoaiTruVienPhiHaoPhiFilter($data);
        $data = $this->bangKeVViewRepository->applyStatusFilter($data, $this->params->status);

        $count = null;
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [
            "total",
            "heinServiceTypeName",
            "tdlServiceName",
        ];
        $data = $this->bangKeVViewRepository->customizeHeinServiceTypeNameTongHop($data); // Lặp qua để đổi các heinServieType thành Thuốc hao phí trong phẫu thuật và Vật tư hao phí trong phẫu thuật
        $data = $this->bangKeVViewRepository->applyGroupByFieldBieuMau($data, $groupBy, $this->params->tab);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bangKeVViewRepository->applyJoins()
            ->where('id', $id);
        $data = $this->bangKeVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNgoaiTruHaoPhi()
    {
        try {
            return $this->getBangKeNgoaiTruHaoPhi();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNgoaiTruBHYTHaoPhi()
    {
        try {
            return $this->getBangKeNgoaiTruBHYTHaoPhi();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNgoaiTruVienPhiTPTB()
    {
        try {
            return $this->getBangKeNgoaiTruVienPhiTPTB();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNgoaiTruBHYTTheoKhoa6556QDBYT()
    {
        try {
            return $this->getBangKeNgoaiTruBHYTTheoKhoa6556QDBYT();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNgoaiTruVienPhiTheoKhoa6556QDBYT()
    {
        try {
            return $this->getBangKeNgoaiTruVienPhiTheoKhoa6556QDBYT();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNoiTruHaoPhi()
    {
        try {
            return $this->getBangKeNoiTruHaoPhi();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNoiTruVienPhiTPTB()
    {
        try {
            return $this->getBangKeNoiTruVienPhiTPTB();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNoiTruBHYTTheoKhoa6556QDBYT()
    {
        try {
            return $this->getBangKeNoiTruBHYTTheoKhoa6556QDBYT();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeNoiTruVienPhiTheoKhoa6556QDBYT()
    {
        try {
            return $this->getBangKeNoiTruVienPhiTheoKhoa6556QDBYT();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function bangKeTongHop6556KhoaPhongThanhToan()
    {
        try {
            return $this->getBangKeTongHop6556KhoaPhongThanhToan();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function tongHopNgoaiTruVienPhiHaoPhi()
    {
        try {
            return $this->getTongHopNgoaiTruVienPhiHaoPhi();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }

    public function updateBangKe($id, $request)
    {

        try {
            $data = $this->bangKeVViewRepository->updateBangKeIds($request, $request->ids, $this->params->time, $this->params->appModifier);
            // return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
}
