<?php

namespace App\Services\Model;

use App\DTOs\RoomVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\RoomVView\InsertRoomVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RoomVViewRepository;

class RoomVViewService
{
    protected $roomVViewRepository;
    protected $params;
    public function __construct(RoomVViewRepository $roomVViewRepository)
    {
        $this->roomVViewRepository = $roomVViewRepository;
    }
    public function withParams(RoomVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->roomVViewRepository->applyJoins();
            $data = $this->roomVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->roomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->roomVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->roomVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->roomVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->roomVViewRepository->applyJoins();
            $data = $this->roomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->roomVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);

            $count = $data->count();
            $data = $this->roomVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->roomVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->roomVViewRepository->applyJoins()
                ->where('v_his_room.id', $id);
            $data = $this->roomVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->roomVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_v_view'], $e);
        }
    }

    // public function createRoomVView($request)
    // {
    //     try {
    //         $data = $this->roomVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->roomVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertRoomVViewIndex($data, $this->params->roomVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['room_v_view'], $e);
    //     }
    // }

    // public function updateRoomVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->roomVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->roomVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->roomVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertRoomVViewIndex($data, $this->params->roomVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['room_v_view'], $e);
    //     }
    // }

    // public function deleteRoomVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->roomVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->roomVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->roomVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->roomVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['room_v_view'], $e);
    //     }
    // }
}
