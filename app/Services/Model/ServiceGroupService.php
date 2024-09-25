<?php

namespace App\Services\Model;

use App\DTOs\ServiceGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceGroup\InsertServiceGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceGroupRepository;

class ServiceGroupService 
{
    protected $serviceGroupRepository;
    protected $params;
    public function __construct(ServiceGroupRepository $serviceGroupRepository)
    {
        $this->serviceGroupRepository = $serviceGroupRepository;
    }
    public function withParams(ServiceGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceGroupRepository->applyJoins();
            $data = $this->serviceGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->serviceGroupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->serviceGroupRepository->applyJoins();
                $data = $this->serviceGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->serviceGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->serviceGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->serviceGroupRepository->applyJoins()
                    ->where('his_service_group.id', $id);
                $data = $this->serviceGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_group'], $e);
        }
    }

    // public function createServiceGroup($request)
    // {
    //     try {
    //         $data = $this->serviceGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceGroupName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertServiceGroupIndex($data, $this->params->serviceGroupName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_group'], $e);
    //     }
    // }

    // public function updateServiceGroup($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->serviceGroupRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->serviceGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceGroupName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertServiceGroupIndex($data, $this->params->serviceGroupName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_group'], $e);
    //     }
    // }

    // public function deleteServiceGroup($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->serviceGroupRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->serviceGroupRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->serviceGroupName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->serviceGroupName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['service_group'], $e);
    //     }
    // }
}
