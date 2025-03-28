<?php

namespace App\Services\Model;

use App\DTOs\PatientTypeAllowDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PatientTypeAllow\InsertPatientTypeAllowIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientTypeAllowRepository;
use Illuminate\Support\Facades\Redis;

class PatientTypeAllowService 
{
    protected $patientTypeAllowRepository;
    protected $params;
    public function __construct(PatientTypeAllowRepository $patientTypeAllowRepository)
    {
        $this->patientTypeAllowRepository = $patientTypeAllowRepository;
    }
    public function withParams(PatientTypeAllowDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->patientTypeAllowRepository->applyJoins();
            $data = $this->patientTypeAllowRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->patientTypeAllowRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->patientTypeAllowRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->patientTypeAllowRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_allow'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->patientTypeAllowName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->patientTypeAllowName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->patientTypeAllowRepository->applyJoins();
                $data = $this->patientTypeAllowRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->patientTypeAllowRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->patientTypeAllowRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_allow'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->patientTypeAllowName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->patientTypeAllowRepository->applyJoins()
                    ->where('his_patient_type_allow.id', $id);
                $data = $this->patientTypeAllowRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_allow'], $e);
        }
    }

    public function createPatientTypeAllow($request)
    {
        try {
            $data = $this->patientTypeAllowRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPatientTypeAllowIndex($data, $this->params->patientTypeAllowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientTypeAllowName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_allow'], $e);
        }
    }

    public function updatePatientTypeAllow($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientTypeAllowRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientTypeAllowRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPatientTypeAllowIndex($data, $this->params->patientTypeAllowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientTypeAllowName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_allow'], $e);
        }
    }

    public function deletePatientTypeAllow($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientTypeAllowRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientTypeAllowRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->patientTypeAllowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientTypeAllowName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type_allow'], $e);
        }
    }
}
