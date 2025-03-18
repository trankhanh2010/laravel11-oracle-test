<?php

namespace App\Services\Model;

use App\DTOs\TreatmentBedRoomListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentBedRoomListVView\InsertTreatmentBedRoomListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentBedRoomListVViewRepository;

class TreatmentBedRoomListVViewService
{
    protected $treatmentBedRoomListVViewRepository;
    protected $params;
    public function __construct(TreatmentBedRoomListVViewRepository $treatmentBedRoomListVViewRepository)
    {
        $this->treatmentBedRoomListVViewRepository = $treatmentBedRoomListVViewRepository;
    }
    public function withParams(TreatmentBedRoomListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentBedRoomListVViewRepository->applyJoins();
            $data = $this->treatmentBedRoomListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentBedRoomListVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
            $data = $this->treatmentBedRoomListVViewRepository->applyExecuteRoomCodeFilter($data, $this->params->executeRoomCode);
            $data = $this->treatmentBedRoomListVViewRepository->applyExecuteRoomIdsFilter($data, $this->params->executeRoomIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsInBedFilter($data, $this->params->isInBed);
            $data = $this->treatmentBedRoomListVViewRepository->applyBedRoomIdsFilter($data, $this->params->bedRoomIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsCoTreatDepartmentFilter($data, $this->params->isCoTreatDepartment);
            $data = $this->treatmentBedRoomListVViewRepository->applyPatientClassifyIdsFilter($data, $this->params->patientClassifyIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsOutFilter($data, $this->params->isOut);
            $data = $this->treatmentBedRoomListVViewRepository->applyAddLoginnameFilter($data, $this->params->addLoginname);
            $data = $this->treatmentBedRoomListVViewRepository->applyAddTimeFromFilter($data, $this->params->addTimeFrom);
            $data = $this->treatmentBedRoomListVViewRepository->applyAddTimeToFilter($data, $this->params->addTimeTo);

            $count = $data->count();
            $data = $this->treatmentBedRoomListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentBedRoomListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
             // Group theo field
            $data = $this->treatmentBedRoomListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->treatmentBedRoomListVViewRepository->applyJoins();
            $data = $this->treatmentBedRoomListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentBedRoomListVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
            $data = $this->treatmentBedRoomListVViewRepository->applyExecuteRoomCodeFilter($data, $this->params->executeRoomCode);
            $data = $this->treatmentBedRoomListVViewRepository->applyExecuteRoomIdsFilter($data, $this->params->executeRoomIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsInBedFilter($data, $this->params->isInBed);
            $data = $this->treatmentBedRoomListVViewRepository->applyBedRoomIdsFilter($data, $this->params->bedRoomIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsCoTreatDepartmentFilter($data, $this->params->isCoTreatDepartment);
            $data = $this->treatmentBedRoomListVViewRepository->applyPatientClassifyIdsFilter($data, $this->params->patientClassifyIds);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsOutFilter($data, $this->params->isOut);
            $data = $this->treatmentBedRoomListVViewRepository->applyAddLoginnameFilter($data, $this->params->addLoginname);
            $data = $this->treatmentBedRoomListVViewRepository->applyAddTimeFromFilter($data, $this->params->addTimeFrom);
            $data = $this->treatmentBedRoomListVViewRepository->applyAddTimeToFilter($data, $this->params->addTimeTo);

            $count = $data->count();
            $data = $this->treatmentBedRoomListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentBedRoomListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->treatmentBedRoomListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->treatmentBedRoomListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentBedRoomListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_list_v_view'], $e);
        }
    }
}
