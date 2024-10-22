<?php

namespace App\Services\Model;

use App\DTOs\SereServExtDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServExt\InsertSereServExtIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServExtRepository;

class SereServExtService
{
    protected $sereServExtRepository;
    protected $params;
    public function __construct(SereServExtRepository $sereServExtRepository)
    {
        $this->sereServExtRepository = $sereServExtRepository;
    }
    public function withParams(SereServExtDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServExtRepository->applyJoins();
            $data = $this->sereServExtRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServExtRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServExtRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServExtRepository->applySereServIdsFilter($data, $this->params->sereServIds);
            $count = $data->count();
            $data = $this->sereServExtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServExtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_ext'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->sereServExtRepository->applyJoins();
            $data = $this->sereServExtRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServExtRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServExtRepository->applySereServIdsFilter($data, $this->params->sereServIds);
            $count = $data->count();
            $data = $this->sereServExtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServExtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_ext'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServExtRepository->applyJoins()
                ->where('his_sere_serv_ext.id', $id);
            $data = $this->sereServExtRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServExtRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_ext'], $e);
        }
    }

    // public function createSereServExt($request)
    // {
    //     try {
    //         $data = $this->sereServExtRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServExtName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServExtIndex($data, $this->params->sereServExtName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_ext'], $e);
    //     }
    // }

    // public function updateSereServExt($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServExtRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServExtRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServExtName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServExtIndex($data, $this->params->sereServExtName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_ext'], $e);
    //     }
    // }

    // public function deleteSereServExt($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServExtRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServExtRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServExtName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServExtName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_ext'], $e);
    //     }
    // }
}
