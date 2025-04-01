<?php

namespace App\Services\Model;

use App\DTOs\DebateListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DebateListVView\InsertDebateListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DebateListVViewRepository;

class DebateListVViewService
{
    protected $debateListVViewRepository;
    protected $params;
    public function __construct(DebateListVViewRepository $debateListVViewRepository)
    {
        $this->debateListVViewRepository = $debateListVViewRepository;
    }
    public function withParams(DebateListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateListVViewRepository->applyJoins();
            $data = $this->debateListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->debateListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->debateListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->debateListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $data = $this->debateListVViewRepository->applyDepartmentIdsFilter($data, $this->params->departmentIds);
            $data = $this->debateListVViewRepository->applyDebateTimeFilter($data, $this->params->debateTimeFrom, $this->params->debateTimeTo);
            $count = $data->count();
            $data = $this->debateListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->debateListVViewRepository->applyJoins();
        $data = $this->debateListVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->debateListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->debateListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->debateListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        $data = $this->debateListVViewRepository->applyDepartmentIdsFilter($data, $this->params->departmentIds);
        $data = $this->debateListVViewRepository->applyDebateTimeFilter($data, $this->params->debateTimeFrom, $this->params->debateTimeTo);
        $count = $data->count();
        $data = $this->debateListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->debateListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->debateListVViewRepository->applyJoins()
        ->where('v_his_debate_list.id', $id);
    $data = $this->debateListVViewRepository->applyIsActiveFilter($data, 1);
    $data = $this->debateListVViewRepository->applyIsDeleteFilter($data, 0);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['debate_list_v_view'], $e);
        }
    }

    // public function createDebateListVView($request)
    // {
    //     try {
    //         $data = $this->debateListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateListVViewIndex($data, $this->params->debateListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_list_v_view'], $e);
    //     }
    // }

    // public function updateDebateListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDebateListVViewIndex($data, $this->params->debateListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_list_v_view'], $e);
    //     }
    // }

    // public function deleteDebateListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->debateListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->debateListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->debateListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->debateListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_list_v_view'], $e);
    //     }
    // }
}
