<?php

namespace App\Services\Model;

use App\DTOs\MedicalCaseCoverListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicalCaseCoverListVView\InsertMedicalCaseCoverListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicalCaseCoverListVViewRepository;

class MedicalCaseCoverListVViewService
{
    protected $medicalCaseCoverListVViewRepository;
    protected $params;
    public function __construct(MedicalCaseCoverListVViewRepository $medicalCaseCoverListVViewRepository)
    {
        $this->medicalCaseCoverListVViewRepository = $medicalCaseCoverListVViewRepository;
    }
    public function withParams(MedicalCaseCoverListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicalCaseCoverListVViewRepository->applyJoins();
            $data = $this->medicalCaseCoverListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->medicalCaseCoverListVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsInBedFilter($data, $this->params->isInBed);
            $data = $this->medicalCaseCoverListVViewRepository->applyBedRoomIdsFilter($data, $this->params->bedRoomIds);
            $data = $this->medicalCaseCoverListVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsCoTreatDepartmentFilter($data, $this->params->isCoTreatDepartment);
            $data = $this->medicalCaseCoverListVViewRepository->applyPatientClassifyIdsFilter($data, $this->params->patientClassifyIds);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsOutFilter($data, $this->params->isOut);
            $data = $this->medicalCaseCoverListVViewRepository->applyAddLoginnameFilter($data, $this->params->addLoginname);
            $data = $this->medicalCaseCoverListVViewRepository->applyAddTimeFromFilter($data, $this->params->addTimeFrom);
            $data = $this->medicalCaseCoverListVViewRepository->applyAddTimeToFilter($data, $this->params->addTimeTo);

            // $count = $data->count();
            $count = null;
            $data = $this->medicalCaseCoverListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicalCaseCoverListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_case_cover_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->medicalCaseCoverListVViewRepository->applyJoins();
            $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->medicalCaseCoverListVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsInBedFilter($data, $this->params->isInBed);
            $data = $this->medicalCaseCoverListVViewRepository->applyBedRoomIdsFilter($data, $this->params->bedRoomIds);
            $data = $this->medicalCaseCoverListVViewRepository->applyTreatmentTypeIdsFilter($data, $this->params->treatmentTypeIds);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsCoTreatDepartmentFilter($data, $this->params->isCoTreatDepartment);
            $data = $this->medicalCaseCoverListVViewRepository->applyPatientClassifyIdsFilter($data, $this->params->patientClassifyIds);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsOutFilter($data, $this->params->isOut);
            $data = $this->medicalCaseCoverListVViewRepository->applyAddLoginnameFilter($data, $this->params->addLoginname);
            $data = $this->medicalCaseCoverListVViewRepository->applyAddTimeFromFilter($data, $this->params->addTimeFrom);
            $data = $this->medicalCaseCoverListVViewRepository->applyAddTimeToFilter($data, $this->params->addTimeTo);

            // $count = $data->count();
            $count = null;
            $data = $this->medicalCaseCoverListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicalCaseCoverListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_case_cover_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->medicalCaseCoverListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->medicalCaseCoverListVViewRepository->applyWithParam($data);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->medicalCaseCoverListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_case_cover_list_v_view'], $e);
        }
    }
}
