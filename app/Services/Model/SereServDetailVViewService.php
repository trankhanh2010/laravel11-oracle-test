<?php

namespace App\Services\Model;

use App\DTOs\SereServDetailVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServDetailVView\InsertSereServDetailVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServDetailVViewRepository;

class SereServDetailVViewService
{
    protected $sereServDetailVViewRepository;
    protected $params;
    public function __construct(SereServDetailVViewRepository $sereServDetailVViewRepository)
    {
        $this->sereServDetailVViewRepository = $sereServDetailVViewRepository;
    }
    public function withParams(SereServDetailVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServDetailVViewRepository->applyJoins($this->params->serviceTypeCode);
            $data = $this->sereServDetailVViewRepository->applyWithParam($data, $this->params->serviceTypeCode);
            $data = $this->sereServDetailVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServDetailVViewRepository->applyIsNoExecuteFilter($data);
            $data = $this->sereServDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServDetailVViewRepository->applyIsDeleteFilter($data, 0);
            $count = $data->count();
            $data = $this->sereServDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_detail_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->sereServDetailVViewRepository->applyJoins($this->params->serviceTypeCode);
        $data = $this->sereServDetailVViewRepository->applyWithParam($data, $this->params->serviceTypeCode);
        $data = $this->sereServDetailVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServDetailVViewRepository->applyIsDeleteFilter($data, 0);
        $count = $data->count();
        $data = $this->sereServDetailVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServDetailVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->sereServDetailVViewRepository->applyJoins($this->params->serviceTypeCode)
        ->where('id', $id);
        $data = $this->sereServDetailVViewRepository->applyWithParam($data, $this->params->serviceTypeCode);
        $data = $this->sereServDetailVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServDetailVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServDetailVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_detail_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_detail_v_view'], $e);
        }
    }

    // public function createSereServDetailVView($request)
    // {
    //     try {
    //         $data = $this->sereServDetailVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServDetailVViewIndex($data, $this->params->sereServDetailVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_detail_v_view'], $e);
    //     }
    // }

    // public function updateSereServDetailVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServDetailVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServDetailVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServDetailVViewIndex($data, $this->params->sereServDetailVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_detail_v_view'], $e);
    //     }
    // }

    // public function deleteSereServDetailVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServDetailVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServDetailVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServDetailVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServDetailVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_detail_v_view'], $e);
    //     }
    // }
}
