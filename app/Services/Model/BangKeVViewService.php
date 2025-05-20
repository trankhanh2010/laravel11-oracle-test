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
        $data = $this->bangKeVViewRepository->applyBangKeNgoaiTruHaoPhi($data);

        $count = $data->count();
        $data = $this->bangKeVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $groupBy = [];
        $data = $this->bangKeVViewRepository->applyGroupByField($data, $groupBy);
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
