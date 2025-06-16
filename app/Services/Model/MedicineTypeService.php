<?php

namespace App\Services\Model;

use App\DTOs\MedicineTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineType\InsertMedicineTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineTypeRepository;
use Illuminate\Support\Facades\Redis;

class MedicineTypeService
{
    protected $medicineTypeRepository;
    protected $params;
    public function __construct(MedicineTypeRepository $medicineTypeRepository)
    {
        $this->medicineTypeRepository = $medicineTypeRepository;
    }
    public function withParams(MedicineTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineTypeRepository->applyJoins();
            $data = $this->medicineTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = null;
            $data = $this->medicineTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->medicineTypeRepository->applyJoins();
        $data = $this->medicineTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = null;
        $data = $this->medicineTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->medicineTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicineTypeRepository->applyJoins()
            ->where('his_medicine_type.id', $id);
        $data = $this->medicineTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->medicineTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicineTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type'], $e);
        }
    }
    public function createMedicineType($request)
    {
        try {
            $data = $this->medicineTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineTypeIndex($data, $this->params->medicineTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type'], $e);
        }
    }

    public function updateMedicineType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineTypeIndex($data, $this->params->medicineTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type'], $e);
        }
    }

    public function deleteMedicineType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineTypeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicineTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type'], $e);
        }
    }
}
