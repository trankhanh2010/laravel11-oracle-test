<?php

namespace App\Services\Model;

use App\DTOs\ServicePatyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServicePaty\InsertServicePatyIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServicePatyRepository;

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
            $data = $this->servicePatyRepository->applyEffectiveFilter($data, $this->params->effective);
            $count = $data->count();
            $data = $this->servicePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->servicePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->servicePatyName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_effective_' . $this->params->effective . '_package_id_' . $this->params->packageId . '_service_id_' . $this->params->serviceId . '_patient_type_ids_' . arrayToCustomStringNotKey($this->params->patientTypeIds ?? []) . '_service_type_ids_' . arrayToCustomStringNotKey($this->params->serviceTypeIds ?? []) . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->servicePatyRepository->applyJoins();
                $data = $this->servicePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->servicePatyRepository->applyServiceTypeIdsFilter($data, $this->params->serviceTypeIds);
                $data = $this->servicePatyRepository->applyPatientTypeIdsFilter($data, $this->params->patientTypeIds);
                $data = $this->servicePatyRepository->applyServiceIdFilter($data, $this->params->serviceId);
                $data = $this->servicePatyRepository->applyPackageIdFilter($data, $this->params->packageId);
                $data = $this->servicePatyRepository->applyEffectiveFilter($data, $this->params->effective);
                $count = $data->count();
                $data = $this->servicePatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->servicePatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->servicePatyName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->servicePatyRepository->applyJoins()
                    ->where('his_service_paty.id', $id);
                $data = $this->servicePatyRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
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
                    // Gọi event để xóa cache
                    event(new DeleteCache($this->params->servicePatyName));
                    // Gọi event để thêm index vào elastic
                    event(new InsertServicePatyIndex($data, $this->params->servicePatyName));
                    $param[] = $data;
                }
            }
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
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->servicePatyName));
            // Gọi event để thêm index vào elastic
            event(new InsertServicePatyIndex($data, $this->params->servicePatyName));
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
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->servicePatyName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->servicePatyName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_paty'], $e);
        }
    }
}