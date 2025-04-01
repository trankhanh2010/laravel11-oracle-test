<?php

namespace App\Services\Model;

use App\DTOs\TreatmentTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentType\InsertTreatmentTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentTypeRepository;
use Illuminate\Support\Facades\Redis;

class TreatmentTypeService
{
    protected $treatmentTypeRepository;
    protected $params;
    public function __construct(TreatmentTypeRepository $treatmentTypeRepository)
    {
        $this->treatmentTypeRepository = $treatmentTypeRepository;
    }
    public function withParams(TreatmentTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentTypeRepository->applyJoins();
            $data = $this->treatmentTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->treatmentTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentTypeRepository->applyJoins();
        $data = $this->treatmentTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->treatmentTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentTypeRepository->applyJoins()
            ->where('his_treatment_type.id', $id);
        $data = $this->treatmentTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->treatmentTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->treatmentTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->treatmentTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->treatmentTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_type'], $e);
        }
    }

    public function createTreatmentType($request)
    {
        try {
            $data = $this->treatmentTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentTypeIndex($data, $this->params->treatmentTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_type'], $e);
        }
    }

    public function updateTreatmentType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTreatmentTypeIndex($data, $this->params->treatmentTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_type'], $e);
        }
    }

    public function deleteTreatmentType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->treatmentTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->treatmentTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->treatmentTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->treatmentTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_type'], $e);
        }
    }
}
