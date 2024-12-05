<?php

namespace App\Services\Model;

use App\DTOs\ServiceConditionDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceCondition\InsertServiceConditionIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceConditionRepository;

class ServiceConditionService 
{
    protected $serviceConditionRepository;
    protected $params;
    public function __construct(ServiceConditionRepository $serviceConditionRepository)
    {
        $this->serviceConditionRepository = $serviceConditionRepository;
    }
    public function withParams(ServiceConditionDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceConditionRepository->applyJoins();
            $data = $this->serviceConditionRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->serviceConditionRepository->applyServiceIdFilter($data, $this->params->serviceId);
            $count = $data->count();
            $data = $this->serviceConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->serviceConditionName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_service_id_' .$this->params->serviceId. '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->serviceConditionRepository->applyJoins();
                $data = $this->serviceConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->serviceConditionRepository->applyServiceIdFilter($data, $this->params->serviceId);
                $count = $data->count();
                $data = $this->serviceConditionRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceConditionRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->serviceConditionName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->serviceConditionRepository->applyJoins()
                    ->where('his_service_condition.id', $id);
                $data = $this->serviceConditionRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }

    public function createServiceCondition($request)
    {
        try {
            $data = $this->serviceConditionRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertServiceConditionIndex($data, $this->params->serviceConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceConditionName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }

    public function updateServiceCondition($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceConditionRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertServiceConditionIndex($data, $this->params->serviceConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceConditionName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }

    public function deleteServiceCondition($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceConditionRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceConditionRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceConditionName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceConditionName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_condition'], $e);
        }
    }
}
