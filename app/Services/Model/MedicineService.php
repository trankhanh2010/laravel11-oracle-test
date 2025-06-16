<?php

namespace App\Services\Model;

use App\DTOs\MedicineDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Medicine\InsertMedicineIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineRepository;
use Illuminate\Support\Facades\Redis;

class MedicineService
{
    protected $medicineRepository;
    protected $params;
    public function __construct(MedicineRepository $medicineRepository)
    {
        $this->medicineRepository = $medicineRepository;
    }
    public function withParams(MedicineDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            if ($this->params->tab == 'keDonThuocPhongKham') {
                $data = $this->medicineRepository->applyJoinsKeDonThuocPhongKham();
            } else {
                $data = $this->medicineRepository->applyJoins();
            }
            $data = $this->medicineRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            if ($this->params->tab == 'keDonThuocPhongKham') {
                $orderBy = [
                    'parent_name' => 'asc',
                ];
                $orderByJoin = ['parent_name'];
                $data = $this->medicineRepository->applyOrdering($data, $orderBy, $orderByJoin);
            } else {
                $data = $this->medicineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            }
            $data = $this->medicineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            if ($this->params->tab == 'keDonThuocPhongKham') {
                $groupBy = [
                    'parentName',
                    'medicineTypeName',
                    'mediStockName',
                ];
                $data = $this->medicineRepository->applyGroupByField($data, $groupBy);
            }
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        if ($this->params->tab == 'keDonThuocPhongKham') {
            $data = $this->medicineRepository->applyJoinsKeDonThuocPhongKham();
        } else {
            $data = $this->medicineRepository->applyJoins();
        }
        $data = $this->medicineRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        if ($this->params->tab == 'keDonThuocPhongKham') {
            $orderBy = [
                'parent_name' => 'asc',
            ];
            $orderByJoin = ['parent_name'];
            $data = $this->medicineRepository->applyOrdering($data, $orderBy, $orderByJoin);
        } else {
            $data = $this->medicineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        }
        $data = $this->medicineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        if ($this->params->tab == 'keDonThuocPhongKham') {
            $groupBy = [
                'parentName',
                'medicineTypeName',
                'mediStockName',
            ];
            $data = $this->medicineRepository->applyGroupByField($data, $groupBy);
        }
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicineRepository->applyJoins()
            ->where('his_medicine.id', $id);
        $data = $this->medicineRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->medicineName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->medicineName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->medicineName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
    public function createMedicine($request)
    {
        try {
            $data = $this->medicineRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineIndex($data, $this->params->medicineName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }

    public function updateMedicine($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertMedicineIndex($data, $this->params->medicineName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }

    public function deleteMedicine($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->medicineRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->medicineRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->medicineName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->medicineName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine'], $e);
        }
    }
}
