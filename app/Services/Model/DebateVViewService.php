<?php

namespace App\Services\Model;

use App\DTOs\DebateVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateVView\InsertDebateVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateVViewRepository;

class DebateVViewService
{
    protected $debateVViewRepository;
    protected $params;
    public function __construct(DebateVViewRepository $debateVViewRepository)
    {
        $this->debateVViewRepository = $debateVViewRepository;
    }
    public function withParams(DebateVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateVViewRepository->applyJoins();
            $data = $this->debateVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->debateVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->debateVViewRepository->applyJoins();
        $data = $this->debateVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->debateVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->debateVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->debateVViewRepository->applyJoins()
        ->where('v_his_debate.id', $id);
    $data = $this->debateVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
        }
    }

    public function createDebateVView($request)
    {
        try {
            $data = $this->debateVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDebateVViewIndex($data, $this->params->debateVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateVViewName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
        }
    }

    public function updateDebateVView($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDebateVViewIndex($data, $this->params->debateVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateVViewName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
        }
    }

    public function deleteDebateVView($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateVViewRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->debateVViewName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateVViewName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
        }
    }
}
