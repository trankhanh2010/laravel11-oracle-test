<?php

namespace App\Services\Model;

use App\DTOs\ExecuteRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExecuteRoom\InsertExecuteRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExecuteRoomRepository;

class ExecuteRoomService 
{
    protected $executeRoomRepository;
    protected $params;
    public function __construct(ExecuteRoomRepository $executeRoomRepository)
    {
        $this->executeRoomRepository = $executeRoomRepository;
    }
    public function withParams(ExecuteRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->executeRoomRepository->applyJoins();
            $data = $this->executeRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->executeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->executeRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->executeRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->executeRoomName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->executeRoomRepository->applyJoins();
                $data = $this->executeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->executeRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->executeRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->executeRoomName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->executeRoomRepository->applyJoins()
                    ->where('his_execute_room.id', $id);
                $data = $this->executeRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }

    public function createExecuteRoom($request)
    {
        try {
            $data = $this->executeRoomRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoomName));
            // Gọi event để thêm index vào elastic
            event(new InsertExecuteRoomIndex($data, $this->params->executeRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }

    public function updateExecuteRoom($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoomRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoomName));
            // Gọi event để thêm index vào elastic
            event(new InsertExecuteRoomIndex($data, $this->params->executeRoomName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }

    public function deleteExecuteRoom($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->executeRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->executeRoomRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->executeRoomName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->executeRoomName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['execute_room'], $e);
        }
    }
}