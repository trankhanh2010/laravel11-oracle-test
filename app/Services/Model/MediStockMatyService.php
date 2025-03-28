<?php

namespace App\Services\Model;

use App\DTOs\MediStockMatyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediStockMaty\InsertMediStockMatyIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\MaterialTypeRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MediStockMatyRepository;
use App\Repositories\MediStockRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MediStockMatyService
{
    protected $mediStockMatyRepository;
    protected $mediStockRepository;
    protected $materialTypeRepository;
    protected $params;
    public function __construct(MediStockMatyRepository $mediStockMatyRepository, MediStockRepository $mediStockRepository, MaterialTypeRepository $materialTypeRepository)
    {
        $this->mediStockMatyRepository = $mediStockMatyRepository;
        $this->mediStockRepository = $mediStockRepository;
        $this->materialTypeRepository = $materialTypeRepository;
    }
    public function withParams(MediStockMatyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mediStockMatyRepository->applyJoins();
            $data = $this->mediStockMatyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mediStockMatyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->mediStockMatyRepository->applyMaterialTypeIdFilter($data, $this->params->materialTypeId);
            $data = $this->mediStockMatyRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
            $count = $data->count();
            $data = $this->mediStockMatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mediStockMatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_maty'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->mediStockMatyName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->mediStockMatyName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->mediStockMatyRepository->applyJoins();
                $data = $this->mediStockMatyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->mediStockMatyRepository->applyMaterialTypeIdFilter($data, $this->params->materialTypeId);
                $data = $this->mediStockMatyRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
                $count = $data->count();
                $data = $this->mediStockMatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->mediStockMatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_maty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->mediStockMatyName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->mediStockMatyRepository->applyJoins()
                    ->where('his_medi_stock_maty.id', $id);
                $data = $this->mediStockMatyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_maty'], $e);
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
            'is_prevent_max' => $request->is_prevent_max,
            'is_goods_restrict' => $request->is_goods_restrict,
        ];
    }
    public function createMediStockMaty($request)
    {
        try {
            if ($request->material_type_id != null) {
                $id = $request->material_type_id;
                $data = $this->materialTypeRepository->getById($id);
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
                        $deleteIds = $this->mediStockMatyRepository->deleteByMaterialTypeId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mediStockMatyName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mediStockMatyRepository->getByMaterialTypeIdAndMediStockIds($id, $medi_stock_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMediStockMatyIndex($item, $this->params->mediStockMatyName));
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
                    if ($request->material_type_ids !== null) {
                        $material_type_ids_arr = explode(',', $request->material_type_ids);
                        foreach ($material_type_ids_arr as $key => $item) {
                            $material_type_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->material_types()->sync($material_type_ids_arr_data);
                    } else {
                        $deleteIds = $this->mediStockMatyRepository->deleteByMediStockId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mediStockMatyName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mediStockMatyRepository->getByMediStockIdAndMaterialTypeIds($id, $material_type_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMediStockMatyIndex($item, $this->params->mediStockMatyName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->mediStockMatyName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock_maty'], $e);
        }
    }
}
