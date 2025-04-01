<?php

namespace App\Services\Model;

use App\DTOs\TreatmentLViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentLView\InsertTreatmentLViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentLViewRepository;

class TreatmentLViewService
{
    protected $treatmentLViewRepository;
    protected $params;
    public function __construct(TreatmentLViewRepository $treatmentLViewRepository)
    {
        $this->treatmentLViewRepository = $treatmentLViewRepository;
    }
    public function withParams(TreatmentLViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentLViewRepository->view();
            $data = $this->treatmentLViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            // $data = $this->treatmentLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->treatmentLViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            $count = $data->count();
            $data = $this->treatmentLViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentLViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_l_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentLViewRepository->view();
        $data = $this->treatmentLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        // $data = $this->treatmentLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->treatmentLViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $count = $data->count();
        $data = $this->treatmentLViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentLViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentLViewRepository->view()
        ->where('l_his_treatment.id', $id);
    $data = $this->treatmentLViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    // $data = $this->treatmentLViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_l_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_l_view'], $e);
        }
    }

    // public function createTreatmentLView($request)
    // {
    //     try {
    //         $data = $this->treatmentLViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentLViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentLViewIndex($data, $this->params->treatmentLViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_l_view'], $e);
    //     }
    // }

    // public function updateTreatmentLView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentLViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentLViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentLViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentLViewIndex($data, $this->params->treatmentLViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_l_view'], $e);
    //     }
    // }

    // public function deleteTreatmentLView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentLViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentLViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentLViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentLViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_l_view'], $e);
    //     }
    // }
}
