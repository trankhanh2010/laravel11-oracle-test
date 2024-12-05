<?php

namespace App\Services\Model;

use App\DTOs\ServiceFollowDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceFollow\InsertServiceFollowIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceFollowRepository;

class ServiceFollowService 
{
    protected $serviceFollowRepository;
    protected $params;
    public function __construct(ServiceFollowRepository $serviceFollowRepository)
    {
        $this->serviceFollowRepository = $serviceFollowRepository;
    }
    public function withParams(ServiceFollowDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceFollowRepository->applyJoins();
            $data = $this->serviceFollowRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceFollowRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceFollowRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceFollowRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->serviceFollowName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->serviceFollowRepository->applyJoins();
                $data = $this->serviceFollowRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->serviceFollowRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceFollowRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->serviceFollowName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->serviceFollowRepository->applyJoins()
                    ->where('his_service_follow.id', $id);
                $data = $this->serviceFollowRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }

    public function createServiceFollow($request)
    {
        try {
            $data = $this->serviceFollowRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertServiceFollowIndex($data, $this->params->serviceFollowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceFollowName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }

    public function updateServiceFollow($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceFollowRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceFollowRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertServiceFollowIndex($data, $this->params->serviceFollowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceFollowName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }

    public function deleteServiceFollow($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceFollowRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceFollowRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceFollowName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceFollowName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_follow'], $e);
        }
    }
}
