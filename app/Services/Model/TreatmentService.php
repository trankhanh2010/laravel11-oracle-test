<?php

namespace App\Services\Model;

use App\DTOs\TreatmentDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Treatment\InsertTreatmentIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentRepository;

class TreatmentService
{
    protected $treatmentRepository;
    protected $params;
    public function __construct(TreatmentRepository $treatmentRepository)
    {
        $this->treatmentRepository = $treatmentRepository;
    }
    public function withParams(TreatmentDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    // public function handleDataBaseSearch()
    // {
    //     try {
    //         $data = $this->treatmentRepository->applyJoins();
    //         $data = $this->treatmentRepository->applyKeywordFilter($data, $this->params->keyword);
    //         $data = $this->treatmentRepository->applyIsActiveFilter($data, $this->params->isActive);
    //         $data = $this->treatmentRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    //         $count = $data->count();
    //         $data = $this->treatmentRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
    //         $data = $this->treatmentRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
    //         return ['data' => $data, 'count' => $count];
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment'], $e);
    //     }
    // }
    public function handleDataBaseTreatmentWithPatientTypeInfoSdoGetAll($id)
    {
        try {
            $data = $this->treatmentRepository->applyJoinsTreatmentWithPatientTypeInfoSdo()
            ->where('his_treatment.id', $id);
            $data = $this->treatmentRepository->applyWith($data);
            $data = $this->treatmentRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->treatmentRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment'], $e);
        }
    }
    public function handleDataBaseTreatmentWithPatientTypeInfoSdoGetWithId($id)
    {
        try {
            $data = $this->treatmentRepository->applyJoinsTreatmentWithPatientTypeInfoSdo()
                ->where('his_treatment.id', $id);
            $data = $this->treatmentRepository->applyWith($data);
            $data = $this->treatmentRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->treatmentRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment'], $e);
        }
    }

    // public function createTreatment($request)
    // {
    //     try {
    //         $data = $this->treatmentRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentIndex($data, $this->params->treatmentName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment'], $e);
    //     }
    // }

    // public function updateTreatment($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentIndex($data, $this->params->treatmentName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment'], $e);
    //     }
    // }

    // public function deleteTreatment($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment'], $e);
    //     }
    // }
}
