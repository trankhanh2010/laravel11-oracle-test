<?php

namespace App\Services\Model;

use App\DTOs\PatientCaseDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PatientCase\InsertPatientCaseIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientCaseRepository;

class PatientCaseService 
{
    protected $patientCaseRepository;
    protected $params;
    public function __construct(PatientCaseRepository $patientCaseRepository)
    {
        $this->patientCaseRepository = $patientCaseRepository;
    }
    public function withParams(PatientCaseDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->patientCaseRepository->applyJoins();
            $data = $this->patientCaseRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->patientCaseRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->patientCaseRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->patientCaseRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_case'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->patientCaseName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->patientCaseRepository->applyJoins();
                $data = $this->patientCaseRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->patientCaseRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->patientCaseRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_case'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->patientCaseName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->patientCaseRepository->applyJoins()
                    ->where('his_patient_case.id', $id);
                $data = $this->patientCaseRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_case'], $e);
        }
    }
    public function createPatientCase($request)
    {
        try {
            $data = $this->patientCaseRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPatientCaseIndex($data, $this->params->patientCaseName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientCaseName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_case'], $e);
        }
    }

    public function updatePatientCase($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientCaseRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientCaseRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPatientCaseIndex($data, $this->params->patientCaseName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientCaseName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_case'], $e);
        }
    }

    public function deletePatientCase($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientCaseRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientCaseRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->patientCaseName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientCaseName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_case'], $e);
        }
    }
}
