<?php

namespace App\Services\Model;

use App\DTOs\MedicineUseFormDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineUseForm\InsertMedicineUseFormIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineUseFormRepository;
use Illuminate\Support\Facades\Redis;

class MedicineUseFormService
{
    protected $medicineUseFormRepository;
    protected $params;
    public function __construct(MedicineUseFormRepository $medicineUseFormRepository)
    {
        $this->medicineUseFormRepository = $medicineUseFormRepository;
    }
    public function withParams(MedicineUseFormDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineUseFormRepository->applyJoins();
            $data = $this->medicineUseFormRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineUseFormRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicineUseFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineUseFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->medicineUseFormRepository->applyJoins();
        $data = $this->medicineUseFormRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->medicineUseFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->medicineUseFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicineUseFormRepository->applyJoins()
            ->where('his_medicine_use_form.id', $id);
        $data = $this->medicineUseFormRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->medicineUseFormName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineUseFormName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicineUseFormName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineUseFormName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }

    public function createMedicineUseForm($request)
    {
        try {
            $data = $this->medicineUseFormRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineUseFormIndex($data, $this->params->medicineUseFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineUseFormName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }

    public function updateMedicineUseForm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineUseFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineUseFormRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineUseFormIndex($data, $this->params->medicineUseFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineUseFormName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }

    public function deleteMedicineUseForm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineUseFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineUseFormRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicineUseFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineUseFormName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_use_form'], $e);
        }
    }
}
