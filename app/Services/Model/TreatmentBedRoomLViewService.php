<?php

namespace App\Services\Model;

use App\DTOs\TreatmentBedRoomLViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentBedRoomLView\InsertTreatmentBedRoomLViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentBedRoomLViewRepository;

class TreatmentBedRoomLViewService
{
    protected $treatmentBedRoomLViewRepository;
    protected $params;
    public function __construct(TreatmentBedRoomLViewRepository $treatmentBedRoomLViewRepository)
    {
        $this->treatmentBedRoomLViewRepository = $treatmentBedRoomLViewRepository;
    }
    public function withParams(TreatmentBedRoomLViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentBedRoomLViewRepository->applyJoins();
            $data = $this->treatmentBedRoomLViewRepository->applyKeywordFilter($data, $this->params->keyword);
            // $data = $this->treatmentBedRoomLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            // $data = $this->treatmentBedRoomLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentBedRoomLViewRepository->applyBedRoomIdsFilter($data, $this->params->bedRoomIds);
            $data = $this->treatmentBedRoomLViewRepository->applyAddTimeToFilter($data, $this->params->addTimeTo);
            $data = $this->treatmentBedRoomLViewRepository->applyIsInRoomFilter($data, $this->params->isInRoom, $this->params->addTimeFrom);
            $count = $data->count();
            $data = $this->treatmentBedRoomLViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentBedRoomLViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_l_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentBedRoomLViewRepository->applyJoins();
        // $data = $this->treatmentBedRoomLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        // $data = $this->treatmentBedRoomLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->treatmentBedRoomLViewRepository->applyBedRoomIdsFilter($data, $this->params->bedRoomIds);
        $data = $this->treatmentBedRoomLViewRepository->applyAddTimeToFilter($data, $this->params->addTimeTo);
        $data = $this->treatmentBedRoomLViewRepository->applyIsInRoomFilter($data, $this->params->isInRoom, $this->params->addTimeFrom);
        $count = $data->count();
        $data = $this->treatmentBedRoomLViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentBedRoomLViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentBedRoomLViewRepository->applyJoins()
        ->where('l_his_treatment_bed_room.id', $id);
    // $data = $this->treatmentBedRoomLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    // $data = $this->treatmentBedRoomLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_l_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_l_view'], $e);
        }
    }

    // public function createTreatmentBedRoomLView($request)
    // {
    //     try {
    //         $data = $this->treatmentBedRoomLViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentBedRoomLViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentBedRoomLViewIndex($data, $this->params->treatmentBedRoomLViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_l_view'], $e);
    //     }
    // }

    // public function updateTreatmentBedRoomLView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentBedRoomLViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentBedRoomLViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentBedRoomLViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentBedRoomLViewIndex($data, $this->params->treatmentBedRoomLViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_l_view'], $e);
    //     }
    // }

    // public function deleteTreatmentBedRoomLView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentBedRoomLViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentBedRoomLViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentBedRoomLViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentBedRoomLViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_bed_room_l_view'], $e);
    //     }
    // }
}
