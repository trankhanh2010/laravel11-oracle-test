<?php

namespace App\Services\Model;

use App\DTOs\SereServDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServ\InsertSereServIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServRepository;

class SereServService
{
    protected $sereServRepository;
    protected $params;
    public function __construct(SereServRepository $sereServRepository)
    {
        $this->sereServRepository = $sereServRepository;
    }
    public function withParams(SereServDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServRepository->applyJoins();
            $data = $this->sereServRepository->applyWith($data);
            $data = $this->sereServRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->sereServRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServRepository->applyJoins();
            $data = $this->sereServRepository->applyWith($data);
            $data = $this->sereServRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->sereServRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServRepository->applyJoins()
                ->where('his_sere_serv.id', $id);
            $data = $this->sereServRepository->applyWith($data);
            $data = $this->sereServRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv'], $e);
        }
    }

    // public function createSereServ($request)
    // {
    //     try {
    //         $data = $this->sereServRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServIndex($data, $this->params->sereServName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv'], $e);
    //     }
    // }

    // public function updateSereServ($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServIndex($data, $this->params->sereServName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv'], $e);
    //     }
    // }

    // public function deleteSereServ($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv'], $e);
    //     }
    // }
}
