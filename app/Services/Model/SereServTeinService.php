<?php

namespace App\Services\Model;

use App\DTOs\SereServTeinDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServTein\InsertSereServTeinIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServTeinRepository;

class SereServTeinService
{
    protected $sereServTeinRepository;
    protected $params;
    public function __construct(SereServTeinRepository $sereServTeinRepository)
    {
        $this->sereServTeinRepository = $sereServTeinRepository;
    }
    public function withParams(SereServTeinDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->sereServTeinRepository->applyJoins();
            $data = $this->sereServTeinRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->sereServTeinRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServTeinRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->sereServTeinRepository->applyTestIndexIdsFilter($data, $this->params->testIndexIds);
            $data = $this->sereServTeinRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
            $count = $data->count();
            $data = $this->sereServTeinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->sereServTeinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->sereServTeinRepository->applyJoins();
        $data = $this->sereServTeinRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServTeinRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->sereServTeinRepository->applyTestIndexIdsFilter($data, $this->params->testIndexIds);
        $data = $this->sereServTeinRepository->applyTdlTreatmentIdFilter($data, $this->params->tdlTreatmentId);
        $count = $data->count();
        $data = $this->sereServTeinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServTeinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->sereServTeinRepository->applyJoins()
        ->where('his_sere_serv_tein.id', $id);
    $data = $this->sereServTeinRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->sereServTeinRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein'], $e);
        }
    }

    // public function createSereServTein($request)
    // {
    //     try {
    //         $data = $this->sereServTeinRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinIndex($data, $this->params->sereServTeinName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein'], $e);
    //     }
    // }

    // public function updateSereServTein($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinIndex($data, $this->params->sereServTeinName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein'], $e);
    //     }
    // }

    // public function deleteSereServTein($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServTeinName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein'], $e);
    //     }
    // }
}
