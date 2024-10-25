<?php

namespace App\Services\Model;

use App\DTOs\SereServDepositVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServDepositVView\InsertSereServDepositVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServDepositVViewRepository;

class SereServDepositVViewService
{
    protected $sereServDepositVViewRepository;
    protected $params;
    public function __construct(SereServDepositVViewRepository $sereServDepositVViewRepository)
    {
        $this->sereServDepositVViewRepository = $sereServDepositVViewRepository;
    }
    public function withParams(SereServDepositVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServDepositVViewRepository->applyJoins();
            $data = $this->sereServDepositVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServDepositVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServDepositVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServDepositVViewRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->sereServDepositVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServDepositVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_deposit_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServDepositVViewRepository->applyJoins();
            $data = $this->sereServDepositVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServDepositVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServDepositVViewRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->sereServDepositVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServDepositVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_deposit_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServDepositVViewRepository->applyJoins()
                ->where('v_his_sere_serv_deposit.id', $id);
            $data = $this->sereServDepositVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServDepositVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_deposit_v_view'], $e);
        }
    }

    // public function createSereServDepositVView($request)
    // {
    //     try {
    //         $data = $this->sereServDepositVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServDepositVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServDepositVViewIndex($data, $this->params->sereServDepositVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_deposit_v_view'], $e);
    //     }
    // }

    // public function updateSereServDepositVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServDepositVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServDepositVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServDepositVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServDepositVViewIndex($data, $this->params->sereServDepositVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_deposit_v_view'], $e);
    //     }
    // }

    // public function deleteSereServDepositVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServDepositVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServDepositVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServDepositVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServDepositVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_deposit_v_view'], $e);
    //     }
    // }
}
