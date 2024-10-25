<?php

namespace App\Services\Model;

use App\DTOs\SeseDepoRepayVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SeseDepoRepayVView\InsertSeseDepoRepayVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SeseDepoRepayVViewRepository;

class SeseDepoRepayVViewService
{
    protected $seseDepoRepayVViewRepository;
    protected $params;
    public function __construct(SeseDepoRepayVViewRepository $seseDepoRepayVViewRepository)
    {
        $this->seseDepoRepayVViewRepository = $seseDepoRepayVViewRepository;
    }
    public function withParams(SeseDepoRepayVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->seseDepoRepayVViewRepository->applyJoins();
            $data = $this->seseDepoRepayVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->seseDepoRepayVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->seseDepoRepayVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->seseDepoRepayVViewRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->seseDepoRepayVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->seseDepoRepayVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sese_depo_repay_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->seseDepoRepayVViewRepository->applyJoins();
            $data = $this->seseDepoRepayVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->seseDepoRepayVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->seseDepoRepayVViewRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->seseDepoRepayVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->seseDepoRepayVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sese_depo_repay_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->seseDepoRepayVViewRepository->applyJoins()
                ->where('v_his_sese_depo_repay.id', $id);
            $data = $this->seseDepoRepayVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->seseDepoRepayVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sese_depo_repay_v_view'], $e);
        }
    }

    // public function createSeseDepoRepayVView($request)
    // {
    //     try {
    //         $data = $this->seseDepoRepayVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->seseDepoRepayVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSeseDepoRepayVViewIndex($data, $this->params->seseDepoRepayVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sese_depo_repay_v_view'], $e);
    //     }
    // }

    // public function updateSeseDepoRepayVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->seseDepoRepayVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->seseDepoRepayVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->seseDepoRepayVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSeseDepoRepayVViewIndex($data, $this->params->seseDepoRepayVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sese_depo_repay_v_view'], $e);
    //     }
    // }

    // public function deleteSeseDepoRepayVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->seseDepoRepayVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->seseDepoRepayVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->seseDepoRepayVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->seseDepoRepayVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sese_depo_repay_v_view'], $e);
    //     }
    // }
}
