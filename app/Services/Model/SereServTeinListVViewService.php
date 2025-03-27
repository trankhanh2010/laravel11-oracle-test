<?php

namespace App\Services\Model;

use App\DTOs\SereServTeinListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServTeinListVView\InsertSereServTeinListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServTeinListVViewRepository;

class SereServTeinListVViewService
{
    protected $sereServTeinListVViewRepository;
    protected $params;
    public function __construct(SereServTeinListVViewRepository $sereServTeinListVViewRepository)
    {
        $this->sereServTeinListVViewRepository = $sereServTeinListVViewRepository;
    }
    public function withParams(SereServTeinListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServTeinListVViewRepository->applyJoins();
            $data = $this->sereServTeinListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServTeinListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServTeinListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
            $data = $this->sereServTeinListVViewRepository->applySereServIdsFilter($data, $this->params->sereServIds);

            // $count = $data->count();
            $count = null;
            $data = $this->sereServTeinListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServTeinListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->sereServTeinListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServTeinListVViewRepository->applyJoins();
            $data = $this->sereServTeinListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $this->sereServTeinListVViewRepository->applyServiceReqIdFilter($data, $this->params->serviceReqId);
            $data = $this->sereServTeinListVViewRepository->applySereServIdsFilter($data, $this->params->sereServIds);

            // $count = $data->count();
            $count = null;
            $data = $this->sereServTeinListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServTeinListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            // Group theo field
            $data = $this->sereServTeinListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServTeinListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->sereServTeinListVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinListVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_list_v_view'], $e);
        }
    }

    // public function createSereServTeinListVView($request)
    // {
    //     try {
    //         $data = $this->sereServTeinListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinListVViewIndex($data, $this->params->sereServTeinListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_list_v_view'], $e);
    //     }
    // }

    // public function updateSereServTeinListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinListVViewIndex($data, $this->params->sereServTeinListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_list_v_view'], $e);
    //     }
    // }

    // public function deleteSereServTeinListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServTeinListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_list_v_view'], $e);
    //     }
    // }
}
