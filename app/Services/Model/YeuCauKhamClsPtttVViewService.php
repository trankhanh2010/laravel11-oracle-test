<?php

namespace App\Services\Model;

use App\DTOs\YeuCauKhamClsPtttVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\YeuCauKhamClsPtttVView\InsertYeuCauKhamClsPtttVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\YeuCauKhamClsPtttVViewRepository;

class YeuCauKhamClsPtttVViewService
{
    protected $yeuCauKhamClsPtttVViewRepository;
    protected $params;
    public function __construct(YeuCauKhamClsPtttVViewRepository $yeuCauKhamClsPtttVViewRepository)
    {
        $this->yeuCauKhamClsPtttVViewRepository = $yeuCauKhamClsPtttVViewRepository;
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
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyServiceReqCodeFilter($data, $this->params->serviceReqCode);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyBedCodeFilter($data, $this->params->bedCode);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiFilter($data, $this->params->trangThai);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiVienPhiFilter($data, $this->params->trangThaiVienPhi);
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiKeThuocFilter($data, $this->params->trangThaiKeThuoc);
            $count = $data->count();
            $data = $this->yeuCauKhamClsPtttVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
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
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyExecuteRoomIdFilter($data, $this->params->executeRoomId);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyServiceReqCodeFilter($data, $this->params->serviceReqCode);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyBedCodeFilter($data, $this->params->bedCode);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiFilter($data, $this->params->trangThai);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiVienPhiFilter($data, $this->params->trangThaiVienPhi);
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyTrangThaiKeThuocFilter($data, $this->params->trangThaiKeThuoc);
        $count = $data->count();
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->yeuCauKhamClsPtttVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->yeuCauKhamClsPtttVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsActiveFilter($data, 1);
    $data = $this->yeuCauKhamClsPtttVViewRepository->applyIsDeleteFilter($data, 0);
    $data = $data->first();
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
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['yeu_cau_kham_cls_pttt_v_view'], $e);
        }
    }

}
