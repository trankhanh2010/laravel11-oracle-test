<?php

namespace App\Services\Model;

use App\DTOs\PatientTypeAlterVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PatientTypeAlterVView\InsertPatientTypeAlterVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientTypeAlterVViewRepository;

class PatientTypeAlterVViewService
{
    protected $patientTypeAlterVViewRepository;
    protected $params;
    public function __construct(PatientTypeAlterVViewRepository $patientTypeAlterVViewRepository)
    {
        $this->patientTypeAlterVViewRepository = $patientTypeAlterVViewRepository;
    }
    public function withParams(PatientTypeAlterVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->patientTypeAlterVViewRepository->view();
            $data = $this->patientTypeAlterVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->patientTypeAlterVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->patientTypeAlterVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->patientTypeAlterVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->patientTypeAlterVViewRepository->applyLogTimeToFilter($data, $this->params->logTimeTo);
            $count = $data->count();
            $data = $this->patientTypeAlterVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->patientTypeAlterVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_alter_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->patientTypeAlterVViewRepository->view();
        $data = $this->patientTypeAlterVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->patientTypeAlterVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->patientTypeAlterVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->patientTypeAlterVViewRepository->applyLogTimeToFilter($data, $this->params->logTimeTo);
        $count = $data->count();
        $data = $this->patientTypeAlterVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->patientTypeAlterVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->patientTypeAlterVViewRepository->view()
        ->where('v_his_patient_type_alter.id', $id);
    $data = $this->patientTypeAlterVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->patientTypeAlterVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_alter_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_alter_v_view'], $e);
        }
    }

    // public function createPatientTypeAlterVView($request)
    // {
    //     try {
    //         $data = $this->patientTypeAlterVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->patientTypeAlterVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertPatientTypeAlterVViewIndex($data, $this->params->patientTypeAlterVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['patient_type_alter_v_view'], $e);
    //     }
    // }

    // public function updatePatientTypeAlterVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->patientTypeAlterVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->patientTypeAlterVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->patientTypeAlterVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertPatientTypeAlterVViewIndex($data, $this->params->patientTypeAlterVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['patient_type_alter_v_view'], $e);
    //     }
    // }

    // public function deletePatientTypeAlterVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->patientTypeAlterVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->patientTypeAlterVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->patientTypeAlterVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->patientTypeAlterVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['patient_type_alter_v_view'], $e);
    //     }
    // }
}
