<?php

namespace App\Services\Model;

use App\DTOs\PriorityTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PriorityType\InsertPriorityTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PriorityTypeRepository;

class PriorityTypeService 
{
    protected $priorityTypeRepository;
    protected $params;
    public function __construct(PriorityTypeRepository $priorityTypeRepository)
    {
        $this->priorityTypeRepository = $priorityTypeRepository;
    }
    public function withParams(PriorityTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->priorityTypeRepository->applyJoins();
            $data = $this->priorityTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->priorityTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->priorityTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->priorityTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['priority_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->priorityTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->priorityTypeRepository->applyJoins();
                $data = $this->priorityTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->priorityTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->priorityTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['priority_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->priorityTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->priorityTypeRepository->applyJoins()
                    ->where('his_priority_type.id', $id);
                $data = $this->priorityTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['priority_type'], $e);
        }
    }

    public function createPriorityType($request)
    {
        try {
            $data = $this->priorityTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->priorityTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertPriorityTypeIndex($data, $this->params->priorityTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['priority_type'], $e);
        }
    }

    public function updatePriorityType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->priorityTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->priorityTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->priorityTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertPriorityTypeIndex($data, $this->params->priorityTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['priority_type'], $e);
        }
    }

    public function deletePriorityType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->priorityTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->priorityTypeRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->priorityTypeName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->priorityTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['priority_type'], $e);
        }
    }
}