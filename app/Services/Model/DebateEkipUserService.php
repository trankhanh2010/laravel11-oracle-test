<?php

namespace App\Services\Model;

use App\DTOs\DebateEkipUserDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateEkipUser\InsertDebateEkipUserIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateEkipUserRepository;

class DebateEkipUserService
{
    protected $debateEkipUserRepository;
    protected $params;
    public function __construct(DebateEkipUserRepository $debateEkipUserRepository)
    {
        $this->debateEkipUserRepository = $debateEkipUserRepository;
    }
    public function withParams(DebateEkipUserDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateEkipUserRepository->view();
            $data = $this->debateEkipUserRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateEkipUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateEkipUserRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->debateEkipUserRepository->applyDebateIdFilter($data, $this->params->debateId);
            $count = $data->count();
            $data = $this->debateEkipUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateEkipUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_ekip_user'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->debateEkipUserRepository->view();
            $data = $this->debateEkipUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateEkipUserRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->debateEkipUserRepository->applyDebateIdFilter($data, $this->params->debateId);
            $count = $data->count();
            $data = $this->debateEkipUserRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateEkipUserRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_ekip_user'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->debateEkipUserRepository->view()
                ->where('his_debate_ekip_user.id', $id);
            $data = $this->debateEkipUserRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateEkipUserRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_ekip_user'], $e);
        }
    }

    // public function createDebateEkipUser($request)
    // {
    //     try {
    //         $data = $this->debateEkipUserRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateEkipUserName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateEkipUserIndex($data, $this->params->debateEkipUserName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_ekip_user'], $e);
    //     }
    // }

    // public function updateDebateEkipUser($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateEkipUserRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateEkipUserRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateEkipUserName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateEkipUserIndex($data, $this->params->debateEkipUserName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_ekip_user'], $e);
    //     }
    // }

    // public function deleteDebateEkipUser($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateEkipUserRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateEkipUserRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateEkipUserName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->debateEkipUserName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_ekip_user'], $e);
    //     }
    // }
}
