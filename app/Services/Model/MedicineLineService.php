<?php

namespace App\Services\Model;

use App\DTOs\MedicineLineDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineLine\InsertMedicineLineIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineLineRepository;
use Illuminate\Support\Facades\Redis;

class MedicineLineService
{
    protected $medicineLineRepository;
    protected $params;
    public function __construct(MedicineLineRepository $medicineLineRepository)
    {
        $this->medicineLineRepository = $medicineLineRepository;
    }
    public function withParams(MedicineLineDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineLineRepository->applyJoins();
            $data = $this->medicineLineRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineLineRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->medicineLineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineLineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->medicineLineRepository->applyJoins();
        $data = $this->medicineLineRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->medicineLineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->medicineLineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicineLineRepository->applyJoins()
            ->where('his_medicine_line.id', $id);
        $data = $this->medicineLineRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->medicineLineName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineLineName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicineLineName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineLineName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
    public function createMedicineLine($request)
    {
        try {
            $data = $this->medicineLineRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineLineIndex($data, $this->params->medicineLineName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineLineName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }

    public function updateMedicineLine($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineLineRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineLineRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineLineIndex($data, $this->params->medicineLineName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineLineName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }

    public function deleteMedicineLine($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineLineRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineLineRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicineLineName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineLineName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_line'], $e);
        }
    }
}
