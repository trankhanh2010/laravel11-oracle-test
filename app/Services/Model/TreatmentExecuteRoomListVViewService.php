<?php

namespace App\Services\Model;

use App\DTOs\TreatmentExecuteRoomListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentExecuteRoomListVView\InsertTreatmentExecuteRoomListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentExecuteRoomListVViewRepository;

class TreatmentExecuteRoomListVViewService
{
    protected $treatmentExecuteRoomListVViewRepository;
    protected $params;
    public function __construct(TreatmentExecuteRoomListVViewRepository $treatmentExecuteRoomListVViewRepository)
    {
        $this->treatmentExecuteRoomListVViewRepository = $treatmentExecuteRoomListVViewRepository;
    }
    public function withParams(TreatmentExecuteRoomListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentExecuteRoomListVViewRepository->applyJoins();
            $data = $this->treatmentExecuteRoomListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyIsNoExecuteFilter($data);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);

            if ($this->params->treatmentCode == null && $this->params->patientCode == null) {
                $data = $this->treatmentExecuteRoomListVViewRepository->applyExecuteRoomIdsFilter($data, $this->params->executeRoomIds);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyServiceReqSttCodesFilter($data, $this->params->serviceReqSttCodes);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyServiceReqSttIdsFilter($data, $this->params->serviceReqSttIds);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
                // $data = $this->treatmentExecuteRoomListVViewRepository->applyIsCoTreatDepartmentFilter($data, $this->params->isCoTreatDepartment);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyPatientClassifyIdsFilter($data, $this->params->patientClassifyIds);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyIsOutFilter($data, $this->params->isOut);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyAddLoginnameFilter($data, $this->params->addLoginname);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
                $data = $this->treatmentExecuteRoomListVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            }
            if(($this->params->start == 0 && !$this->params->getAll) || $this->params->isCount){
                $count = $data->count();
            }else{
                $count = null;
            }
            
            $data = $this->treatmentExecuteRoomListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentExecuteRoomListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->treatmentExecuteRoomListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_execute_room_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentExecuteRoomListVViewRepository->applyJoins();
        $data = $this->treatmentExecuteRoomListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyExecuteRoomIdsFilter($data, $this->params->executeRoomIds);

        if ($this->params->treatmentCode == null && $this->params->patientCode == null) {
            $data = $this->treatmentExecuteRoomListVViewRepository->applyServiceReqSttCodesFilter($data, $this->params->serviceReqSttCodes);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyServiceReqSttIdsFilter($data, $this->params->serviceReqSttIds);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            // $data = $this->treatmentExecuteRoomListVViewRepository->applyIsCoTreatDepartmentFilter($data, $this->params->isCoTreatDepartment);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyPatientClassifyIdsFilter($data, $this->params->patientClassifyIds);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyIsOutFilter($data, $this->params->isOut);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyAddLoginnameFilter($data, $this->params->addLoginname);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->treatmentExecuteRoomListVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        }
        if(($this->params->start == 0 && !$this->params->getAll) || $this->params->isCount){
            $count = $data->count();
        }else{
            $count = null;
        }
        $data = $this->treatmentExecuteRoomListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentExecuteRoomListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->treatmentExecuteRoomListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentExecuteRoomListVViewRepository->applyJoins()
        ->where('id', $id);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->treatmentExecuteRoomListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_execute_room_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_execute_room_list_v_view'], $e);
        }
    }
}
