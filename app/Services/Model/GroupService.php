<?php

namespace App\Services\Model;

use App\DTOs\GroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Group\InsertGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\GroupRepository;

class GroupService 
{
    protected $groupRepository;
    protected $params;
    public function __construct(GroupRepository $groupRepository)
    {
        $this->groupRepository = $groupRepository;
    }
    public function withParams(GroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->groupRepository->applyJoins();
            $data = $this->groupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->groupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->groupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->groupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->groupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->groupRepository->applyJoins();
                $data = $this->groupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->groupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->groupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->groupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->groupRepository->applyJoins()
                    ->where('sda_group.id', $id);
                $data = $this->groupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group'], $e);
        }
    }
    public function createGroup($request)
    {
        try {
            $data = $this->groupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->groupName));
            // Gọi event để thêm index vào elastic
            event(new InsertGroupIndex($data, $this->params->groupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group'], $e);
        }
    }

    public function updateGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->groupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->groupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->groupName));
            // Gọi event để thêm index vào elastic
            event(new InsertGroupIndex($data, $this->params->groupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group'], $e);
        }
    }

    public function deleteGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->groupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->groupRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->groupName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->groupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['group'], $e);
        }
    }
}
