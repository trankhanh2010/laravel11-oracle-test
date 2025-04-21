<?php

namespace App\Services\Model;

use App\DTOs\MedicineListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineListVView\InsertMedicineListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MedicineListVViewRepository;

class MedicineListVViewService
{
    protected $medicineListVViewRepository;
    protected $params;
    public function __construct(MedicineListVViewRepository $medicineListVViewRepository)
    {
        $this->medicineListVViewRepository = $medicineListVViewRepository;
    }
    public function withParams(MedicineListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->medicineListVViewRepository->applyJoins();
            $data = $this->medicineListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->medicineListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->medicineListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->medicineListVViewRepository->applyServiceTypeCodeTHFilter($data);
            $data = $this->medicineListVViewRepository->applyTabFilter($data, $this->params->tab);
            $data = $this->medicineListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            $data = $this->medicineListVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
            $data = $this->medicineListVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
            $count = $data->count();
            $data = $this->medicineListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->medicineListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->medicineListVViewRepository->applyJoins();
        $data = $this->medicineListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->medicineListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->medicineListVViewRepository->applyServiceTypeCodeTHFilter($data);
        $data = $this->medicineListVViewRepository->applyTabFilter($data, $this->params->tab);
        $data = $this->medicineListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->medicineListVViewRepository->applyIntructionTimeFromFilter($data, $this->params->intructionTimeFrom);
        $data = $this->medicineListVViewRepository->applyIntructionTimeToFilter($data, $this->params->intructionTimeTo);
        $count = $data->count();
        $data = $this->medicineListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->medicineListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->medicineListVViewRepository->applyJoins()
        ->where('v_his_medicine_list.id', $id);
    $data = $this->medicineListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->medicineListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medicine_list_v_view'], $e);
        }
    }

    // public function createMedicineListVView($request)
    // {
    //     try {
    //         $data = $this->medicineListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->medicineListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertMedicineListVViewIndex($data, $this->params->medicineListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['medicine_list_v_view'], $e);
    //     }
    // }

    // public function updateMedicineListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->medicineListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->medicineListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->medicineListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertMedicineListVViewIndex($data, $this->params->medicineListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['medicine_list_v_view'], $e);
    //     }
    // }

    // public function deleteMedicineListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->medicineListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->medicineListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->medicineListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->medicineListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['medicine_list_v_view'], $e);
    //     }
    // }
}
