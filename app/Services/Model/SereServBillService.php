<?php

namespace App\Services\Model;

use App\DTOs\SereServBillDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServBill\InsertSereServBillIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServBillRepository;

class SereServBillService
{
    protected $sereServBillRepository;
    protected $params;
    public function __construct(SereServBillRepository $sereServBillRepository)
    {
        $this->sereServBillRepository = $sereServBillRepository;
    }
    public function withParams(SereServBillDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServBillRepository->applyJoins();
            $data = $this->sereServBillRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServBillRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServBillRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServBillRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->sereServBillRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServBillRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServBillRepository->applyJoins();
            $data = $this->sereServBillRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServBillRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServBillRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->sereServBillRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServBillRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServBillRepository->applyJoins()
                ->where('his_sere_serv_bill.id', $id);
            $data = $this->sereServBillRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServBillRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
        }
    }

    // public function createSereServBill($request)
    // {
    //     try {
    //         $data = $this->sereServBillRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServBillName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServBillIndex($data, $this->params->sereServBillName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }

    // public function updateSereServBill($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServBillRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServBillRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServBillName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServBillIndex($data, $this->params->sereServBillName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }

    // public function deleteSereServBill($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServBillRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServBillRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServBillName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServBillName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_bill'], $e);
    //     }
    // }
}
