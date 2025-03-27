<?php

namespace App\Services\Model;

use App\DTOs\SereServTeinVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServTeinVView\InsertSereServTeinVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServTeinVViewRepository;

class SereServTeinVViewService
{
    protected $sereServTeinVViewRepository;
    protected $params;
    public function __construct(SereServTeinVViewRepository $sereServTeinVViewRepository)
    {
        $this->sereServTeinVViewRepository = $sereServTeinVViewRepository;
    }
    public function withParams(SereServTeinVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServTeinVViewRepository->applyJoins();
            $data = $this->sereServTeinVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServTeinVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServTeinVViewRepository->applySereServIdsFilter($data, $this->params->sereServIds);
            $count = $data->count();
            $data = $this->sereServTeinVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServTeinVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServTeinVViewRepository->applyJoins();
            $data = $this->sereServTeinVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServTeinVViewRepository->applySereServIdsFilter($data, $this->params->sereServIds);
            $count = $data->count();
            $data = $this->sereServTeinVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServTeinVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServTeinVViewRepository->applyJoins()
                ->where('v_his_sere_serv_tein.id', $id);
            $data = $this->sereServTeinVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_v_view'], $e);
        }
    }

    // public function createSereServTeinVView($request)
    // {
    //     try {
    //         $data = $this->sereServTeinVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinVViewIndex($data, $this->params->sereServTeinVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_v_view'], $e);
    //     }
    // }

    // public function updateSereServTeinVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinVViewIndex($data, $this->params->sereServTeinVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_v_view'], $e);
    //     }
    // }

    // public function deleteSereServTeinVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServTeinVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_v_view'], $e);
    //     }
    // }
}
