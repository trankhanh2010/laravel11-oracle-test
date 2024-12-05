<?php

namespace App\Services\Model;

use App\DTOs\UserRoomVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\UserRoomVView\InsertUserRoomVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\UserRoomVViewRepository;

class UserRoomVViewService
{
    protected $userRoomVViewRepository;
    protected $params;
    public function __construct(UserRoomVViewRepository $userRoomVViewRepository)
    {
        $this->userRoomVViewRepository = $userRoomVViewRepository;
    }
    public function withParams(UserRoomVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->userRoomVViewRepository->applyJoins();
            $data = $this->userRoomVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->userRoomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->userRoomVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userRoomVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->userRoomVViewRepository->applyJoins();
            $data = $this->userRoomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->userRoomVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userRoomVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->userRoomVViewRepository->applyJoins()
                ->where('v_his_user_room.id', $id);
            $data = $this->userRoomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }

    public function createUserRoomVView($request)
    {
        try {
            $data = $this->userRoomVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertUserRoomVViewIndex($data, $this->params->userRoomVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->userRoomVViewName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }

    public function updateUserRoomVView($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->userRoomVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->userRoomVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertUserRoomVViewIndex($data, $this->params->userRoomVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->userRoomVViewName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }

    public function deleteUserRoomVView($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->userRoomVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->userRoomVViewRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->userRoomVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->userRoomVViewName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room_v_view'], $e);
        }
    }
}
