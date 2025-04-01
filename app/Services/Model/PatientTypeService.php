<?php

namespace App\Services\Model;

use App\DTOs\PatientTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PatientType\InsertPatientTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PatientTypeRepository;
use Illuminate\Support\Facades\Redis;

class PatientTypeService
{
    protected $patientTypeRepository;
    protected $params;
    public function __construct(PatientTypeRepository $patientTypeRepository)
    {
        $this->patientTypeRepository = $patientTypeRepository;
    }
    public function withParams(PatientTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->patientTypeRepository->applyJoins();
            $data = $this->patientTypeRepository->applyWith($data);
            $data = $this->patientTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->patientTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->patientTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->patientTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->patientTypeRepository->applyJoins();
        $data = $this->patientTypeRepository->applyWith($data);
        $data = $this->patientTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->patientTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->patientTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->patientTypeRepository->applyJoins()
            ->where('his_patient_type.id', $id);
        $data = $this->patientTypeRepository->applyWith($data);
        $data = $this->patientTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->patientTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->patientTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->patientTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->patientTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type'], $e);
        }
    }

    public function createPatientType($request)
    {
        try {
            $data = $this->patientTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPatientTypeIndex($data, $this->params->patientTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type'], $e);
        }
    }

    public function updatePatientType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPatientTypeIndex($data, $this->params->patientTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type'], $e);
        }
    }

    public function deletePatientType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->patientTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->patientTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->patientTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->patientTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['patient_type'], $e);
        }
    }
}
