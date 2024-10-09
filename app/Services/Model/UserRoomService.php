<?php

namespace App\Services\Model;

use App\DTOs\UserRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\UserRoom\InsertUserRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\UserRoomRepository;

class UserRoomService
{
    protected $userRoomRepository;
    protected $params;
    public function __construct(UserRoomRepository $userRoomRepository)
    {
        $this->userRoomRepository = $userRoomRepository;
    }
    public function withParams(UserRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->userRoomRepository->view();
            $data = $this->userRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->userRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->userRoomRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->userRoomRepository->applyLoginnameFilter($data, $this->params->loginname);
            $count = $data->count();
            $data = $this->userRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->userRoomRepository->view();
            $data = $this->userRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->userRoomRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->userRoomRepository->applyLoginnameFilter($data, $this->params->loginname);
            $count = $data->count();
            $data = $this->userRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->userRoomRepository->view()
                ->where('his_user_room.id', $id);
            $data = $this->userRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->userRoomRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_room'], $e);
        }
    }

    // public function createUserRoom($request)
    // {
    //     try {
    //         $data = $this->userRoomRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->userRoomName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertUserRoomIndex($data, $this->params->userRoomName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['user_room'], $e);
    //     }
    // }

    // public function updateUserRoom($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->userRoomRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->userRoomRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->userRoomName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertUserRoomIndex($data, $this->params->userRoomName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['user_room'], $e);
    //     }
    // }

    // public function deleteUserRoom($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->userRoomRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->userRoomRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->userRoomName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->userRoomName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['user_room'], $e);
    //     }
    // }
}
