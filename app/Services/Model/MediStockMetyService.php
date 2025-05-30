<?php

namespace App\Services\Model;

use App\DTOs\MediStockMetyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediStockMety\InsertMediStockMetyIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\MedicineTypeRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MediStockMetyRepository;
use App\Repositories\MediStockRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MediStockMetyService
{
    protected $mediStockMetyRepository;
    protected $mediStockRepository;
    protected $medicineTypeRepository;
    protected $params;
    public function __construct(MediStockMetyRepository $mediStockMetyRepository, MediStockRepository $mediStockRepository, MedicineTypeRepository $medicineTypeRepository)
    {
        $this->mediStockMetyRepository = $mediStockMetyRepository;
        $this->mediStockRepository = $mediStockRepository;
        $this->medicineTypeRepository = $medicineTypeRepository;
    }
    public function withParams(MediStockMetyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mediStockMetyRepository->applyJoins();
            $data = $this->mediStockMetyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mediStockMetyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->mediStockMetyRepository->applyMedicineTypeIdFilter($data, $this->params->medicineTypeId);
            $data = $this->mediStockMetyRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
            $count = $data->count();
            $data = $this->mediStockMetyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mediStockMetyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_mety'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->mediStockMetyRepository->applyJoins();
        $data = $this->mediStockMetyRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->mediStockMetyRepository->applyMedicineTypeIdFilter($data, $this->params->medicineTypeId);
        $data = $this->mediStockMetyRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
        $count = $data->count();
        $data = $this->mediStockMetyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->mediStockMetyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->mediStockMetyRepository->applyJoins()
            ->where('his_medi_stock_mety.id', $id);
        $data = $this->mediStockMetyRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->mediStockMetyName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mediStockMetyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_mety'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->mediStockMetyName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mediStockMetyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_mety'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'app_creator' => $this->params->appCreator,
            'app_modifier' => $this->params->appModifier,
            'is_prevent_max' => $request->is_prevent_max,
            'is_prevent_exp' => $request->is_prevent_exp,
            'is_goods_restrict' => $request->is_goods_restrict,
        ];
    }
    public function createMediStockMety($request)
    {
        try {
            if ($request->medicine_type_id != null) {
                $id = $request->medicine_type_id;
                $data = $this->medicineTypeRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->medi_stock_ids !== null) {
                        $medi_stock_ids_arr = explode(',', $request->medi_stock_ids);
                        foreach ($medi_stock_ids_arr as $key => $item) {
                            $medi_stock_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->medi_stocks()->sync($medi_stock_ids_arr_data);
                    } else {
                        $deleteIds = $this->mediStockMetyRepository->deleteByMedicineTypeId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mediStockMetyName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mediStockMetyRepository->getByMedicineTypeIdAndMediStockIds($id, $medi_stock_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMediStockMetyIndex($item, $this->params->mediStockMetyName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->medi_stock_id != null) {
                $id = $request->medi_stock_id;
                $data = $this->mediStockRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->medicine_type_ids !== null) {
                        $medicine_type_ids_arr = explode(',', $request->medicine_type_ids);
                        foreach ($medicine_type_ids_arr as $key => $item) {
                            $medicine_type_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->medicine_types()->sync($medicine_type_ids_arr_data);
                    } else {
                        $deleteIds = $this->mediStockMetyRepository->deleteByMediStockId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mediStockMetyName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mediStockMetyRepository->getByMediStockIdAndMedicineTypeIds($id, $medicine_type_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMediStockMetyIndex($item, $this->params->mediStockMetyName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->mediStockMetyName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_mety'], $e);
        }
    }
}
