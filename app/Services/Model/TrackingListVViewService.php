<?php

namespace App\Services\Model;

use App\DTOs\TrackingListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TrackingListVView\InsertTrackingListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrackingListVViewRepository;

class TrackingListVViewService
{
    protected $trackingListVViewRepository;
    protected $params;
    public function __construct(TrackingListVViewRepository $trackingListVViewRepository)
    {
        $this->trackingListVViewRepository = $trackingListVViewRepository;
    }
    public function withParams(TrackingListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->trackingListVViewRepository->applyJoins();
            $data = $this->trackingListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->trackingListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->trackingListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->trackingListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->trackingListVViewRepository->applyJoins();
        $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = $data->count();
        $data = $this->trackingListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->trackingListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        // Group theo field
        $data = $this->trackingListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->trackingListVViewRepository->applyJoins()
        ->where('id', $id);
        $data = $this->trackingListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingListVViewRepository->applyIsDeleteFilter($data, 0);
    $data = $this->trackingListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
        }
    }

    // public function createTrackingListVView($request)
    // {
    //     try {
    //         $data = $this->trackingListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTrackingListVViewIndex($data, $this->params->trackingListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
    //     }
    // }

    // public function updateTrackingListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->trackingListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->trackingListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTrackingListVViewIndex($data, $this->params->trackingListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
    //     }
    // }

    // public function deleteTrackingListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->trackingListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->trackingListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->trackingListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->trackingListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['tracking_list_v_view'], $e);
    //     }
    // }
}
