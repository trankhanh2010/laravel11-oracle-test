<?php

namespace App\Services\Model;

use App\DTOs\SereServVView4DTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServVView4\InsertSereServVView4Index;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServVView4Repository;

class SereServVView4Service
{
    protected $sereServVView4Repository;
    protected $params;
    public function __construct(SereServVView4Repository $sereServVView4Repository)
    {
        $this->sereServVView4Repository = $sereServVView4Repository;
    }
    public function withParams(SereServVView4DTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServVView4Repository->view();
            $data = $this->sereServVView4Repository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServVView4Repository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServVView4Repository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->sereServVView4Repository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServVView4Repository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_v_view_4'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->sereServVView4Repository->view();
        $data = $this->sereServVView4Repository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServVView4Repository->applyIsDeleteFilter($data, $this->params->isDelete);
        $count = $data->count();
        $data = $this->sereServVView4Repository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServVView4Repository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->sereServVView4Repository->view()
        ->where('v_his_sere_serv_4.id', $id);
    $data = $this->sereServVView4Repository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->sereServVView4Repository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_v_view_4'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_v_view_4'], $e);
        }
    }

    // public function createSereServVView4($request)
    // {
    //     try {
    //         $data = $this->sereServVView4Repository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServVView4Name));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServVView4Index($data, $this->params->sereServVView4Name));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_v_view_4'], $e);
    //     }
    // }

    // public function updateSereServVView4($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServVView4Repository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServVView4Repository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServVView4Name));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServVView4Index($data, $this->params->sereServVView4Name));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_v_view_4'], $e);
    //     }
    // }

    // public function deleteSereServVView4($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServVView4Repository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServVView4Repository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServVView4Name));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServVView4Name));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_v_view_4'], $e);
    //     }
    // }
}
