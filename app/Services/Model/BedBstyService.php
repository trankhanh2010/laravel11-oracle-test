<?php

namespace App\Services\Model;

use App\DTOs\BedBstyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DeleteIndex;
use App\Events\Elastic\BedBsty\InsertBedBstyIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BedBstyRepository;
use App\Repositories\BedRepository;
use App\Repositories\ServiceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class BedBstyService
{
    protected $bedBstyRepository;
    protected $bedRepository;
    protected $serviceRepository;
    protected $params;
    public function __construct(BedBstyRepository $bedBstyRepository, BedRepository $bedRepository, ServiceRepository $serviceRepository)
    {
        $this->bedBstyRepository = $bedBstyRepository;
        $this->bedRepository = $bedRepository;
        $this->serviceRepository = $serviceRepository;
    }
    public function withParams(BedBstyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bedBstyRepository->applyJoins();
            $data = $this->bedBstyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bedBstyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->bedBstyRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
            $data = $this->bedBstyRepository->applyBedIdsFilter($data, $this->params->bedIds);
            $count = $data->count();
            $data = $this->bedBstyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bedBstyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->bedBstyRepository->applyJoins();
        $data = $this->bedBstyRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->bedBstyRepository->applyServiceIdsFilter($data, $this->params->serviceIds);
        $data = $this->bedBstyRepository->applyBedIdsFilter($data, $this->params->bedIds);
        $count = $data->count();
        $data = $this->bedBstyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bedBstyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bedBstyRepository->applyJoins()
            ->where('his_bed_bsty.id', $id);
        $data = $this->bedBstyRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->bedBstyName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bedBstyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->bedBstyName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bedBstyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
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
    public function createBedBsty($request)
    {
        try {
            if ($request->bed_id != null) {
                $id = $request->bed_id;
                $data = $this->bedRepository->getById($id);
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
                        $deleteIds = $this->bedBstyRepository->deleteByBedId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->bedBstyName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->bedBstyRepository->getByBedIdAndServiceIds($id, $service_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertBedBstyIndex($item, $this->params->bedBstyName));
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
                    if ($request->bed_ids !== null) {
                        $bed_ids_arr = explode(',', $request->bed_ids);
                        foreach ($bed_ids_arr as $key => $item) {
                            $bed_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->beds()->sync($bed_ids_arr_data);
                    } else {
                        $deleteIds = $this->bedBstyRepository->deleteByServiceId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->bedBstyName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->bedBstyRepository->getByServiceIdAndBedIds($id, $bed_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertBedBstyIndex($item, $this->params->bedBstyName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->bedBstyName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_bsty'], $e);
        }
    }
}
