<?php

namespace App\Services\Model;

use App\DTOs\ServiceReqTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReqType\InsertServiceReqTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceReqTypeRepository;

class ServiceReqTypeService 
{
    protected $serviceReqTypeRepository;
    protected $params;
    public function __construct(ServiceReqTypeRepository $serviceReqTypeRepository)
    {
        $this->serviceReqTypeRepository = $serviceReqTypeRepository;
    }
    public function withParams(ServiceReqTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceReqTypeRepository->applyJoins();
            $data = $this->serviceReqTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceReqTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceReqTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->serviceReqTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->serviceReqTypeRepository->applyJoins();
                $data = $this->serviceReqTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->serviceReqTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceReqTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->serviceReqTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->serviceReqTypeRepository->applyJoins()
                    ->where('his_service_req_type.id', $id);
                $data = $this->serviceReqTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
    public function createServiceReqType($request)
    {
        try {
            $data = $this->serviceReqTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertServiceReqTypeIndex($data, $this->params->serviceReqTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }

    public function updateServiceReqType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceReqTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceReqTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertServiceReqTypeIndex($data, $this->params->serviceReqTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }

    public function deleteServiceReqType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceReqTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceReqTypeRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqTypeName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceReqTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_type'], $e);
        }
    }
}
