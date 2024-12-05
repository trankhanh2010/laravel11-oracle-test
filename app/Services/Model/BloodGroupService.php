<?php

namespace App\Services\Model;

use App\DTOs\BloodGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BloodGroup\InsertBloodGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BloodGroupRepository;

class BloodGroupService 
{
    protected $bloodGroupRepository;
    protected $params;
    public function __construct(BloodGroupRepository $bloodGroupRepository)
    {
        $this->bloodGroupRepository = $bloodGroupRepository;
    }
    public function withParams(BloodGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bloodGroupRepository->applyJoins();
            $data = $this->bloodGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bloodGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bloodGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bloodGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bloodGroupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->bloodGroupRepository->applyJoins();
                $data = $this->bloodGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bloodGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bloodGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bloodGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bloodGroupRepository->applyJoins()
                    ->where('his_blood_group.id', $id);
                $data = $this->bloodGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_group'], $e);
        }
    }

    public function createBloodGroup($request)
    {
        try {
            $data = $this->bloodGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBloodGroupIndex($data, $this->params->bloodGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bloodGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_group'], $e);
        }
    }

    public function updateBloodGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bloodGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bloodGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBloodGroupIndex($data, $this->params->bloodGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bloodGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_group'], $e);
        }
    }

    public function deleteBloodGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bloodGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bloodGroupRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bloodGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bloodGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_group'], $e);
        }
    }
}
