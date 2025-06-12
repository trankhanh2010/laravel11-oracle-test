<?php

namespace App\Services\Model;

use App\DTOs\ServicePatyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServicePaty\InsertServicePatyIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServicePatyRepository;
use Illuminate\Support\Facades\Redis;

class ServicePatyService
{
    protected $servicePatyRepository;
    protected $params;
    public function __construct(ServicePatyRepository $servicePatyRepository)
    {
        $this->servicePatyRepository = $servicePatyRepository;
    }
    public function withParams(ServicePatyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->servicePatyRepository->applyJoins();
            $data = $this->servicePatyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->servicePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->servicePatyRepository->applyServiceTypeIdsFilter($data, $this->params->serviceTypeIds);
            $data = $this->servicePatyRepository->applyPatientTypeIdsFilter($data, $this->params->patientTypeIds);
            $data = $this->servicePatyRepository->applyServiceIdFilter($data, $this->params->serviceId);
            $data = $this->servicePatyRepository->applyPackageIdFilter($data, $this->params->packageId);
            $data = $this->servicePatyRepository->applyTabFilter($data, $this->params->tab);
            $data = $this->servicePatyRepository->applyEffectiveFilter($data, $this->params->effective);
            $count = $data->count();
            $data = $this->servicePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->servicePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->servicePatyRepository->applyJoins();
        $data = $this->servicePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->servicePatyRepository->applyServiceTypeIdsFilter($data, $this->params->serviceTypeIds);
        $data = $this->servicePatyRepository->applyPatientTypeIdsFilter($data, $this->params->patientTypeIds);
        $data = $this->servicePatyRepository->applyServiceIdFilter($data, $this->params->serviceId);
        $data = $this->servicePatyRepository->applyPackageIdFilter($data, $this->params->packageId);
        $data = $this->servicePatyRepository->applyTabFilter($data, $this->params->tab);
        $data = $this->servicePatyRepository->applyEffectiveFilter($data, $this->params->effective);
        $count = $data->count();
        $data = $this->servicePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->servicePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->servicePatyRepository->applyJoins()
            ->where('his_service_paty.id', $id);
        $data = $this->servicePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->servicePatyName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->servicePatyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->servicePatyName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->servicePatyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }

    public function createServicePaty($request)
    {
        try {
            foreach (explode(',', $request->branch_ids) as $key => $branchId) {
                foreach (explode(',', $request->patient_type_ids) as $key => $patientTypeId) {
                    $data = $this->servicePatyRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier, $branchId, $patientTypeId);

                    // Gọi event để thêm index vào elastic
                    event(new InsertServicePatyIndex($data, $this->params->servicePatyName));
                    $param[] = $data;
                }
            }
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->servicePatyName));
            return returnDataCreateSuccess($param);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }

    public function updateServicePaty($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->servicePatyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->servicePatyRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServicePatyIndex($data, $this->params->servicePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->servicePatyName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }

    public function deleteServicePaty($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->servicePatyRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->servicePatyRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->servicePatyName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->servicePatyName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
    public function getDonGiaVienPhi($serviceId, $inTime)
    {
        try {
            $data = $this->servicePatyRepository->getDonGiaVienPhi($serviceId, $inTime);
            return ['data' => $data, 'count' => null];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
}
