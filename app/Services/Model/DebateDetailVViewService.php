<?php

namespace App\Services\Model;

use App\DTOs\DebateDetailVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateDetailVView\InsertDebateDetailVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateDetailVViewRepository;

class DebateDetailVViewService
{
    protected $debateDetailVViewRepository;
    protected $params;
    public function __construct(DebateDetailVViewRepository $debateDetailVViewRepository)
    {
        $this->debateDetailVViewRepository = $debateDetailVViewRepository;
    }
    public function withParams(DebateDetailVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateDetailVViewRepository->applyJoins();
            $data = $this->debateDetailVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->debateDetailVViewRepository->applyIsDeleteFilter($data, 0);
            $count = $data->count();
            $data = $this->debateDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_detail_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->debateDetailVViewRepository->applyJoins();
        $data = $this->debateDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->debateDetailVViewRepository->applyIsDeleteFilter($data, 0);
        $count = $data->count();
        $data = $this->debateDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->debateDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->debateDetailVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->debateDetailVViewRepository->applyWithParam($data);
    $data = $this->debateDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->debateDetailVViewRepository->applyIsDeleteFilter($data, 0);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_detail_v_view'], $e);
        }
    }

    // public function createDebateDetailVView($request)
    // {
    //     try {
    //         $data = $this->debateDetailVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateDetailVViewIndex($data, $this->params->debateDetailVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_detail_v_view'], $e);
    //     }
    // }

    // public function updateDebateDetailVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateDetailVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateDetailVViewIndex($data, $this->params->debateDetailVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_detail_v_view'], $e);
    //     }
    // }

    // public function deleteDebateDetailVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateDetailVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateDetailVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->debateDetailVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_detail_v_view'], $e);
    //     }
    // }
}
