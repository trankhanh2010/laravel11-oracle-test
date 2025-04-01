<?php

namespace App\Services\Model;

use App\DTOs\TreatmentRoomGroupVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentRoomGroupVView\InsertTreatmentRoomGroupVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentRoomGroupVViewRepository;

class TreatmentRoomGroupVViewService
{
    protected $treatmentRoomGroupVViewRepository;
    protected $params;
    public function __construct(TreatmentRoomGroupVViewRepository $treatmentRoomGroupVViewRepository)
    {
        $this->treatmentRoomGroupVViewRepository = $treatmentRoomGroupVViewRepository;
    }
    public function withParams(TreatmentRoomGroupVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentRoomGroupVViewRepository->applyJoins();
            $data = $this->treatmentRoomGroupVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
            $count = $data->count();
            $data = $this->treatmentRoomGroupVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentRoomGroupVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_room_group_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentRoomGroupVViewRepository->applyJoins();
        $data = $this->treatmentRoomGroupVViewRepository->applyDepartmentCodeFilter($data, $this->params->departmentCode);
        $count = $data->count();
        $data = $this->treatmentRoomGroupVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentRoomGroupVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentRoomGroupVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_room_group_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_room_group_v_view'], $e);
        }
    }

    // public function createTreatmentRoomGroupVView($request)
    // {
    //     try {
    //         $data = $this->treatmentRoomGroupVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentRoomGroupVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentRoomGroupVViewIndex($data, $this->params->treatmentRoomGroupVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_room_group_v_view'], $e);
    //     }
    // }

    // public function updateTreatmentRoomGroupVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentRoomGroupVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentRoomGroupVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentRoomGroupVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentRoomGroupVViewIndex($data, $this->params->treatmentRoomGroupVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_room_group_v_view'], $e);
    //     }
    // }

    // public function deleteTreatmentRoomGroupVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentRoomGroupVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentRoomGroupVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentRoomGroupVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentRoomGroupVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_room_group_v_view'], $e);
    //     }
    // }
}
