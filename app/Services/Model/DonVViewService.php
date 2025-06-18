<?php

namespace App\Services\Model;

use App\DTOs\DonVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DonVView\InsertDonVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DonVViewRepository;

class DonVViewService
{
    protected $donVViewRepository;
    protected $params;
    public function __construct(DonVViewRepository $donVViewRepository)
    {
        $this->donVViewRepository = $donVViewRepository;
    }
    public function withParams(DonVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->donVViewRepository->applyJoins();
            $data = $this->donVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->donVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->donVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->donVViewRepository->applyTabFilter($data, $this->params->tab);
            $data = $this->donVViewRepository->applyPatientIdFilter($data, $this->params->patientId);
            $data = $this->donVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->donVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $count = $data->count();
            $data = $this->donVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->donVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            if ($this->params->tab == 'donCuKeDonThuocPhongKham') {
                $groupBy = [
                    'expMestCode',
                    'tdlServiceReqCode'
                ];
                $data = $this->donVViewRepository->applyGroupByField($data, $groupBy);
            } else {
                $data = $this->donVViewRepository->applyGroupByField($data, $this->params->groupBy);
            }
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->donVViewRepository->applyJoins();
        $data = $this->donVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->donVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->donVViewRepository->applyTabFilter($data, $this->params->tab);
        $data = $this->donVViewRepository->applyPatientIdFilter($data, $this->params->patientId);
        $data = $this->donVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->donVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $count = $data->count();
        $data = $this->donVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->donVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        if ($this->params->tab == 'donCuKeDonThuocPhongKham') {
            $groupBy = [
                'expMestCode',
                'tdlServiceReqCode'
            ];
            $data = $this->donVViewRepository->applyGroupByField($data, $groupBy);
        } else {
            $data = $this->donVViewRepository->applyGroupByField($data, $this->params->groupBy);
        }
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->donVViewRepository->applyJoins()
            ->where('xa_v_his_don.id', $id);
        $data = $this->donVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->donVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
        }
    }

    // public function createDonVView($request)
    // {
    //     try {
    //         $data = $this->donVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->donVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDonVViewIndex($data, $this->params->donVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
    //     }
    // }

    // public function updateDonVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->donVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->donVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->donVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDonVViewIndex($data, $this->params->donVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
    //     }
    // }

    // public function deleteDonVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->donVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->donVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->donVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->donVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['don_v_view'], $e);
    //     }
    // }
}
