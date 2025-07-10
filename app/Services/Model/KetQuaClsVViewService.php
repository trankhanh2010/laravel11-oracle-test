<?php

namespace App\Services\Model;

use App\DTOs\KetQuaClsVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\KetQuaClsVView\InsertKetQuaClsVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\KetQuaClsVViewRepository;

class KetQuaClsVViewService
{
    protected $ketQuaClsVViewRepository;
    protected $params;
    public function __construct(KetQuaClsVViewRepository $ketQuaClsVViewRepository)
    {
        $this->ketQuaClsVViewRepository = $ketQuaClsVViewRepository;
    }
    public function withParams(KetQuaClsVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->ketQuaClsVViewRepository->applyJoins();
            $data = $this->ketQuaClsVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ketQuaClsVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->ketQuaClsVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->ketQuaClsVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->ketQuaClsVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ketQuaClsVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ket_qua_cls_v_view'], $e);
        }
    }
    public function getAllDataFromDatabase()
    {
        try {
            $data = $this->ketQuaClsVViewRepository->applyJoins();
            $data = $this->ketQuaClsVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->ketQuaClsVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->ketQuaClsVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->ketQuaClsVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ketQuaClsVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ket_qua_cls_v_view'], $e);
        }
    }
    private function getAllDataFromDatabaseChonKetQuaCls()
    {
        $data = $this->ketQuaClsVViewRepository->applyJoinsChonKetQuaCls();
        $data = $this->ketQuaClsVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->ketQuaClsVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->ketQuaClsVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->ketQuaClsVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->ketQuaClsVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $count = $data->count();
        $orderBy = [
            "intruction_date" => "desc",
            "test_index_num_order" => "asc",
        ];
        $data = $this->ketQuaClsVViewRepository->applyOrdering($data, $orderBy, []);
        $data = $this->ketQuaClsVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupByFields = [
            'intructionDate',
            'serviceTypeName',
        ];
        $data = $this->ketQuaClsVViewRepository->applyGroupByField($data, $groupByFields, $this->params->hienThiDichVuChaLoaiXN);

        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->ketQuaClsVViewRepository->applyJoins()
        ->where('xa_v_his_ket_qua_cls.id', $id);
    $data = $this->ketQuaClsVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->ketQuaClsVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ket_qua_cls_v_view'], $e);
        }
    }
    public function handleDataBaseGetAllChonKetQuaCls()
    {
        try {
            return $this->getAllDataFromDatabaseChonKetQuaCls();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ket_qua_cls_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ket_qua_cls_v_view'], $e);
        }
    }
}
