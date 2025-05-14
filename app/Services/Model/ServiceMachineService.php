<?php

namespace App\Services\Model;

use App\DTOs\ServiceMachineDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceMachine\InsertServiceMachineIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\MachineRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceMachineRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ServiceMachineService
{
    protected $serviceMachineRepository;
    protected $serviceRepository;
    protected $machineRepository;
    protected $params;
    public function __construct(ServiceMachineRepository $serviceMachineRepository, ServiceRepository $serviceRepository, MachineRepository $machineRepository)
    {
        $this->serviceMachineRepository = $serviceMachineRepository;
        $this->serviceRepository = $serviceRepository;
        $this->machineRepository = $machineRepository;
    }
    public function withParams(ServiceMachineDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceMachineRepository->applyJoins();
            $data = $this->serviceMachineRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceMachineRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceMachineRepository->applyMachineIdFilter($data, $this->params->machineId);
            $data = $this->serviceMachineRepository->applyServiceIdFilter($data, $this->params->serviceId);
            $count = $data->count();
            $data = $this->serviceMachineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceMachineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_machine'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->serviceMachineRepository->applyJoins();
        $data = $this->serviceMachineRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->serviceMachineRepository->applyMachineIdFilter($data, $this->params->machineId);
        $data = $this->serviceMachineRepository->applyServiceIdFilter($data, $this->params->serviceId);
        $count = $data->count();
        $data = $this->serviceMachineRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->serviceMachineRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->serviceMachineRepository->applyJoins()
            ->where('his_service_machine.id', $id);
        $data = $this->serviceMachineRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->serviceMachineName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceMachineName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_machine'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->serviceMachineName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->serviceMachineName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_machine'], $e);
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
        ];
    }
    public function createServiceMachine($request)
    {
        try {
            if ($request->machine_id != null) {
                $id = $request->machine_id;
                $data = $this->machineRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->service_ids !== null) {
                        $service_ids_arr = explode(',', $request->service_ids);
                        foreach ($service_ids_arr as $key => $item) {
                            $service_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->services()->sync($service_ids_arr_data);
                    } else {
                        $deleteIds = $this->serviceMachineRepository->deleteByMachineId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->serviceMachineName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->serviceMachineRepository->getByMachineIdAndServiceIds($id, $service_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertServiceMachineIndex($item, $this->params->serviceMachineName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->service_id != null) {
                $id = $request->service_id;
                $data = $this->serviceRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->machine_ids !== null) {
                        $machine_ids_arr = explode(',', $request->machine_ids);
                        foreach ($machine_ids_arr as $key => $item) {
                            $machine_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->machines()->sync($machine_ids_arr_data);
                    } else {
                        $deleteIds = $this->serviceMachineRepository->deleteByServiceId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->serviceMachineName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->serviceMachineRepository->getByServiceIdAndMachineIds($id, $machine_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertServiceMachineIndex($item, $this->params->serviceMachineName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->serviceMachineName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_machine'], $e);
        }
    }
}
