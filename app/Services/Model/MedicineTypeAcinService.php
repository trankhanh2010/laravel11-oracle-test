<?php

namespace App\Services\Model;

use App\DTOs\MedicineTypeAcinDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineTypeAcin\InsertMedicineTypeAcinIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\ActiveIngredientRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineTypeAcinRepository;
use App\Repositories\MedicineTypeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MedicineTypeAcinService
{
    protected $medicineTypeAcinRepository;
    protected $medicineTypeRepository;
    protected $activeIngredientRepository;
    protected $params;
    public function __construct(MedicineTypeAcinRepository $medicineTypeAcinRepository, MedicineTypeRepository $medicineTypeRepository, ActiveIngredientRepository $activeIngredientRepository)
    {
        $this->medicineTypeAcinRepository = $medicineTypeAcinRepository;
        $this->medicineTypeRepository = $medicineTypeRepository;
        $this->activeIngredientRepository = $activeIngredientRepository;
    }
    public function withParams(MedicineTypeAcinDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineTypeAcinRepository->applyJoins();
            $data = $this->medicineTypeAcinRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineTypeAcinRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->medicineTypeAcinRepository->applyActiveIngredientIdFilter($data, $this->params->activeIngredientId);
            $data = $this->medicineTypeAcinRepository->applyMedicineTypeIdFilter($data, $this->params->medicineTypeId);
            $count = $data->count();
            $data = $this->medicineTypeAcinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineTypeAcinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type_acin'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->medicineTypeAcinName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->medicineTypeAcinName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->medicineTypeAcinRepository->applyJoins();
                $data = $this->medicineTypeAcinRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->medicineTypeAcinRepository->applyActiveIngredientIdFilter($data, $this->params->activeIngredientId);
                $data = $this->medicineTypeAcinRepository->applyMedicineTypeIdFilter($data, $this->params->medicineTypeId);
                $count = $data->count();
                $data = $this->medicineTypeAcinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->medicineTypeAcinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type_acin'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->medicineTypeAcinName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->medicineTypeAcinRepository->applyJoins()
                    ->where('his_medicine_type_acin.id', $id);
                $data = $this->medicineTypeAcinRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type_acin'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'app_creator' => $this->params->appCreator,
            'app_modifier' => $this->params->appModifier,
        ];
    }
    public function createMedicineTypeAcin($request)
    {
        try {
            if ($request->active_ingredient_id != null) {
                $id = $request->active_ingredient_id;
                $data = $this->activeIngredientRepository->getById($id);
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
                        $deleteIds = $this->medicineTypeAcinRepository->deleteByActiveIngredientId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->medicineTypeAcinName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->medicineTypeAcinRepository->getByActiveIngredientIdAndMedicineTypeIds($id, $medicine_type_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMedicineTypeAcinIndex($item, $this->params->medicineTypeAcinName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->medicine_type_id != null) {
                $id = $request->medicine_type_id;
                $data = $this->medicineTypeRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->active_ingredient_ids !== null) {
                        $active_ingredient_ids_arr = explode(',', $request->active_ingredient_ids);
                        foreach ($active_ingredient_ids_arr as $key => $item) {
                            $active_ingredient_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->active_ingredients()->sync($active_ingredient_ids_arr_data);
                    } else {
                        $deleteIds = $this->medicineTypeAcinRepository->deleteByMedicineTypeId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->medicineTypeAcinName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->medicineTypeAcinRepository->getByMedicineTypeIdAndActiveIngredientIds($id, $active_ingredient_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMedicineTypeAcinIndex($item, $this->params->medicineTypeAcinName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->medicineTypeAcinName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_type_acin'], $e);
        }
    }
}
