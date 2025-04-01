<?php

namespace App\Services\Model;

use App\DTOs\MestPatientTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MestPatientType\InsertMestPatientTypeIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\PatientTypeRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MestPatientTypeRepository;
use App\Repositories\MediStockRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MestPatientTypeService
{
    protected $mestPatientTypeRepository;
    protected $mediStockRepository;
    protected $patientTypeRepository;
    protected $params;
    public function __construct(MestPatientTypeRepository $mestPatientTypeRepository, MediStockRepository $mediStockRepository, PatientTypeRepository $patientTypeRepository)
    {
        $this->mestPatientTypeRepository = $mestPatientTypeRepository;
        $this->mediStockRepository = $mediStockRepository;
        $this->patientTypeRepository = $patientTypeRepository;
    }
    public function withParams(MestPatientTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mestPatientTypeRepository->applyJoins();
            $data = $this->mestPatientTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mestPatientTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->mestPatientTypeRepository->applyPatientTypeIdFilter($data, $this->params->patientTypeId);
            $data = $this->mestPatientTypeRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
            $count = $data->count();
            $data = $this->mestPatientTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mestPatientTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_patient_type'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->mestPatientTypeRepository->applyJoins();
        $data = $this->mestPatientTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->mestPatientTypeRepository->applyPatientTypeIdFilter($data, $this->params->patientTypeId);
        $data = $this->mestPatientTypeRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
        $count = $data->count();
        $data = $this->mestPatientTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->mestPatientTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->mestPatientTypeRepository->applyJoins()
            ->where('his_mest_patient_type.id', $id);
        $data = $this->mestPatientTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->mestPatientTypeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mestPatientTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_patient_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->mestPatientTypeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->mestPatientTypeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_patient_type'], $e);
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
    public function createMestPatientType($request)
    {
        try {
            if ($request->patient_type_id != null) {
                $id = $request->patient_type_id;
                $data = $this->patientTypeRepository->getById($id);
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
                        $deleteIds = $this->mestPatientTypeRepository->deleteByPatientTypeId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mestPatientTypeName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mestPatientTypeRepository->getByPatientTypeIdAndMediStockIds($id, $medi_stock_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMestPatientTypeIndex($item, $this->params->mestPatientTypeName));
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
                    if ($request->patient_type_ids !== null) {
                        $patient_type_ids_arr = explode(',', $request->patient_type_ids);
                        foreach ($patient_type_ids_arr as $key => $item) {
                            $patient_type_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->patient_types()->sync($patient_type_ids_arr_data);
                    } else {
                        $deleteIds = $this->mestPatientTypeRepository->deleteByMediStockId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mestPatientTypeName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mestPatientTypeRepository->getByMediStockIdAndPatientTypeIds($id, $patient_type_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMestPatientTypeIndex($item, $this->params->mestPatientTypeName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->mestPatientTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_patient_type'], $e);
        }
    }
}
