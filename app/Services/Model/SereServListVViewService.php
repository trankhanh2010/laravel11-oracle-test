<?php

namespace App\Services\Model;

use App\DTOs\SereServListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServListVView\InsertSereServListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServListVViewRepository;

class SereServListVViewService
{
    protected $sereServListVViewRepository;
    protected $params;
    public function __construct(SereServListVViewRepository $sereServListVViewRepository)
    {
        $this->sereServListVViewRepository = $sereServListVViewRepository;
    }
    public function withParams(SereServListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServListVViewRepository->applyJoins();
            $data = $this->sereServListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
            $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
            $data = $this->sereServListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
            $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->sereServListVViewRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
            $data = $this->sereServListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            $data = $this->sereServListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
            $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
            $data = $this->sereServListVViewRepository->applyNotInTrackingFilter($data, $this->params->notInTracking);

            $count = $data->count();
            $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->sereServListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->sereServListVViewRepository->applyJoins();
        $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
        $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->sereServListVViewRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
        $data = $this->sereServListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->sereServListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
        $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
        $data = $this->sereServListVViewRepository->applyNotInTrackingFilter($data, $this->params->notInTracking);
        
        $count = $data->count();
        $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->sereServListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseSuaChiDinh()
    {
        $data = $this->sereServListVViewRepository->applyJoinsSuaChiDinh();
        $data = $this->sereServListVViewRepository->applyWithParamSuaChiDinh($data);
        $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyTrackingIdFilter($data, $this->params->trackingId);
        $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->sereServListVViewRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
        $data = $this->sereServListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->sereServListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
        $data = $this->sereServListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
        $data = $this->sereServListVViewRepository->applyNotInTrackingFilter($data, $this->params->notInTracking);
        
        $count = $data->count();
        $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->sereServListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseChonThongTinXuLy()
    {
        $data = $this->sereServListVViewRepository->applyJoinsChonThongTinXuLy();
        $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->sereServListVViewRepository->applyUnionAllDichVuDonChonThongTinXuLy($data, $this->params->treatmentId);
        
        $count = null;
        $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServListVViewRepository->fetchDataNotWith($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $this->params->groupBy = [
            'serviceTypeName',
        ];
        $data = $this->sereServListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    public function getAllDataFromDatabaseNotInTracking()
    {
        $data = $this->sereServListVViewRepository->applyJoinsNotInTracking();
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->sereServListVViewRepository->applyNotInTrackingFilter($data, true);
        
        $count = null;
        $this->params->orderBy = ['intruction_time' => 'desc'];
        $data = $this->sereServListVViewRepository->applyOrdering($data, $this->params->orderBy, []);
        $data = $this->sereServListVViewRepository->fetchDataNotWith($data, true, 0, 20);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->sereServListVViewRepository->applyJoins()
        ->where('id', $id);
        $data = $this->sereServListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAllSuaChiDinh()
    {
        try {
            return $this->getAllDataFromDatabaseSuaChiDinh();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAllChonThongTinXuLy()
    {
        try {
            return $this->getAllDataFromDatabaseChonThongTinXuLy();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
        }
    }

    // public function createSereServListVView($request)
    // {
    //     try {
    //         $data = $this->sereServListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServListVViewIndex($data, $this->params->sereServListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
    //     }
    // }

    // public function updateSereServListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServListVViewIndex($data, $this->params->sereServListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
    //     }
    // }

    // public function deleteSereServListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_list_v_view'], $e);
    //     }
    // }
}
