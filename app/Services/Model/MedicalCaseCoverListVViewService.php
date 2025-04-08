<?php

namespace App\Services\Model;

use App\DTOs\MedicalCaseCoverListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicalCaseCoverListVView\InsertMedicalCaseCoverListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicalCaseCoverListVViewRepository;
use Illuminate\Support\Facades\Redis;

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
            $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
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
    private function getAllDataFromDatabase()
    {
        $data = $this->medicalCaseCoverListVViewRepository->applyJoins($this->params->tab);
        $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
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
    }
    private function getDataById($id)
    {
        $data = $this->medicalCaseCoverListVViewRepository->applyJoins($this->params->tab)
            ->where('id', $id);
        $data = $this->medicalCaseCoverListVViewRepository->applyWithParam($data, $this->params->tab);
        $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->medicalCaseCoverListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $data->first();
        return $data;
    }
    private function getDataByCode($code)
    {
        $data = $this->medicalCaseCoverListVViewRepository->applyJoins($this->params->tab)
            ->where('treatment_code', $code);
        $data = $this->medicalCaseCoverListVViewRepository->applyWithParam($data, $this->params->tab);
        $data = $this->medicalCaseCoverListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->medicalCaseCoverListVViewRepository->applyIsDeleteFilter($data, 0);
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
                $cacheKey = $this->params->medicalCaseCoverListVViewName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicalCaseCoverListVViewName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, 6, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_case_cover_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicalCaseCoverListVViewName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicalCaseCoverListVViewName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, 6, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_case_cover_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithCode($code)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataByCode($code);
            } else {
                $cacheKey = $this->params->medicalCaseCoverListVViewName . '_' . $code . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicalCaseCoverListVViewName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, 6, function () use ($code) {
                    return $this->getDataByCode($code);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medical_case_cover_list_v_view'], $e);
        }
    }
}
