<?php

namespace App\Services\Model;

use App\DTOs\RationGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\RationGroup\InsertRationGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RationGroupRepository;

class RationGroupService 
{
    protected $rationGroupRepository;
    protected $params;
    public function __construct(RationGroupRepository $rationGroupRepository)
    {
        $this->rationGroupRepository = $rationGroupRepository;
    }
    public function withParams(RationGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->rationGroupRepository->applyJoins();
            $data = $this->rationGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->rationGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->rationGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->rationGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->rationGroupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->rationGroupRepository->applyJoins();
                $data = $this->rationGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->rationGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->rationGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->rationGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->rationGroupRepository->applyJoins()
                    ->where('his_ration_group.id', $id);
                $data = $this->rationGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }

    public function createRationGroup($request)
    {
        try {
            $data = $this->rationGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationGroupName));
            // Gọi event để thêm index vào elastic
            event(new InsertRationGroupIndex($data, $this->params->rationGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }

    public function updateRationGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->rationGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->rationGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationGroupName));
            // Gọi event để thêm index vào elastic
            event(new InsertRationGroupIndex($data, $this->params->rationGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }

    public function deleteRationGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->rationGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->rationGroupRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->rationGroupName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->rationGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ration_group'], $e);
        }
    }
}