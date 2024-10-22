<?php

namespace App\Services\Model;

use App\DTOs\DebateUserDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateUser\InsertDebateUserIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateUserRepository;

class DebateUserService
{
    protected $debateUserRepository;
    protected $params;
    public function __construct(DebateUserRepository $debateUserRepository)
    {
        $this->debateUserRepository = $debateUserRepository;
    }
    public function withParams(DebateUserDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateUserRepository->applyJoins();
            $data = $this->debateUserRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateUserRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->debateUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->debateUserRepository->applyJoins();
            $data = $this->debateUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateUserRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->debateUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->debateUserRepository->applyJoins()
                ->where('his_debate_user.id', $id);
            $data = $this->debateUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateUserRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
        }
    }

    // public function createDebateUser($request)
    // {
    //     try {
    //         $data = $this->debateUserRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateUserName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateUserIndex($data, $this->params->debateUserName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }

    // public function updateDebateUser($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateUserRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateUserRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateUserName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateUserIndex($data, $this->params->debateUserName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }

    // public function deleteDebateUser($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateUserRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateUserRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateUserName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->debateUserName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_user'], $e);
    //     }
    // }
}
