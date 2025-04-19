<?php

namespace App\Services\Model;

use App\DTOs\ResultClsVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ResultClsVView\InsertResultClsVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ResultClsVViewRepository;

class ResultClsVViewService
{
    protected $resultClsVViewRepository;
    protected $params;
    public function __construct(ResultClsVViewRepository $resultClsVViewRepository)
    {
        $this->resultClsVViewRepository = $resultClsVViewRepository;
    }
    public function withParams(ResultClsVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->resultClsVViewRepository->applyJoins();
            $data = $this->resultClsVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->resultClsVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->resultClsVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->resultClsVViewRepository->applyIsNoExecuteFilter($data);
            $data = $this->resultClsVViewRepository->applyServiceReqIsNoExecuteFilter($data);
            $data = $this->resultClsVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            $data = $this->resultClsVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->resultClsVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);

            $count = $data->count();
            // $count = null;
            $data = $this->resultClsVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->resultClsVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->resultClsVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['result_cls_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->resultClsVViewRepository->applyJoins();
        $data = $this->resultClsVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->resultClsVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->resultClsVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->resultClsVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->resultClsVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->resultClsVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->resultClsVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $count = $data->count();
        // $count = null;
        $data = $this->resultClsVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->resultClsVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->resultClsVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->resultClsVViewRepository->applyJoins()
        ->where('id', $id);
        $data = $this->resultClsVViewRepository->applyIsActiveFilter($data, 1);
        $data = $this->resultClsVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->resultClsVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->resultClsVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['result_cls_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['result_cls_v_view'], $e);
        }
    }

    // public function createResultClsVView($request)
    // {
    //     try {
    //         $data = $this->resultClsVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->resultClsVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertResultClsVViewIndex($data, $this->params->resultClsVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['result_cls_v_view'], $e);
    //     }
    // }

    // public function updateResultClsVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->resultClsVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->resultClsVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->resultClsVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertResultClsVViewIndex($data, $this->params->resultClsVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['result_cls_v_view'], $e);
    //     }
    // }

    // public function deleteResultClsVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->resultClsVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->resultClsVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->resultClsVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->resultClsVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['result_cls_v_view'], $e);
    //     }
    // }
}
