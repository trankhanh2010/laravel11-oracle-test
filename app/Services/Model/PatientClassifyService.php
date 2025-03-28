<?php

namespace App\Services\Model;

use App\DTOs\PatientClassifyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PatientClassify\InsertPatientClassifyIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientClassifyRepository;
use Illuminate\Support\Facades\Redis;

class PatientClassifyService 
{
    protected $patientClassifyRepository;
    protected $params;
    public function __construct(PatientClassifyRepository $patientClassifyRepository)
    {
        $this->patientClassifyRepository = $patientClassifyRepository;
    }
    public function withParams(PatientClassifyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->patientClassifyRepository->applyJoins();
            $data = $this->patientClassifyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->patientClassifyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->patientClassifyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->patientClassifyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_classify'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->patientClassifyName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->patientClassifyName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->patientClassifyRepository->applyJoins();
                $data = $this->patientClassifyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->patientClassifyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->patientClassifyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_classify'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->patientClassifyName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->patientClassifyRepository->applyJoins()
                    ->where('his_patient_classify.id', $id);
                $data = $this->patientClassifyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_classify'], $e);
        }
    }

    public function createPatientClassify($request)
    {
        try {
            $data = $this->patientClassifyRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPatientClassifyIndex($data, $this->params->patientClassifyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientClassifyName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_classify'], $e);
        }
    }

    public function updatePatientClassify($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientClassifyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientClassifyRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPatientClassifyIndex($data, $this->params->patientClassifyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientClassifyName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_classify'], $e);
        }
    }

    public function deletePatientClassify($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientClassifyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientClassifyRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->patientClassifyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientClassifyName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_classify'], $e);
        }
    }
}
