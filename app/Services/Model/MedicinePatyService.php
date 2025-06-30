<?php

namespace App\Services\Model;

use App\DTOs\MedicinePatyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicinePaty\InsertMedicinePatyIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicinePatyRepository;
use Illuminate\Support\Facades\Redis;

class MedicinePatyService
{
    protected $medicinePatyRepository;
    protected $params;
    public function __construct(MedicinePatyRepository $medicinePatyRepository)
    {
        $this->medicinePatyRepository = $medicinePatyRepository;
    }
    public function withParams(MedicinePatyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            if ($this->params->tab == 'getData') {
                $data = $this->medicinePatyRepository->applyJoinsGetData();
            } else {
                $data = $this->medicinePatyRepository->applyJoins();
            }
            $data = $this->medicinePatyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicinePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
            if ($this->params->tab == 'getData') {
                $count = null;
            } else {
                $count = $data->count();
            }
            $data = $this->medicinePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicinePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        if ($this->params->tab == 'getData') {
            $data = $this->medicinePatyRepository->applyJoinsGetData();
        } else {
            $data = $this->medicinePatyRepository->applyJoins();
        }
        $data = $this->medicinePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
        if ($this->params->tab == 'getData') {
            $count = null;
        } else {
            $count = $data->count();
        }
        $data = $this->medicinePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->medicinePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicinePatyRepository->applyJoins()
            ->where('his_medicine_paty.id', $id);
        $data = $this->medicinePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->medicinePatyName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicinePatyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicinePatyName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicinePatyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }

    public function createMedicinePaty($request)
    {
        try {
            $data = $this->medicinePatyRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicinePatyIndex($data, $this->params->medicinePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicinePatyName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }

    public function updateMedicinePaty($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicinePatyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicinePatyRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicinePatyIndex($data, $this->params->medicinePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicinePatyName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }

    public function deleteMedicinePaty($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicinePatyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicinePatyRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicinePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicinePatyName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_paty'], $e);
        }
    }
}
