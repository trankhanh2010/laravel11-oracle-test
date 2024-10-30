<?php

namespace App\Services\Model;

use App\DTOs\CareDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Care\InsertCareIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CareRepository;

class CareService
{
    protected $careRepository;
    protected $params;
    public function __construct(CareRepository $careRepository)
    {
        $this->careRepository = $careRepository;
    }
    public function withParams(CareDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->careRepository->applyJoins();
            $data = $this->careRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->careRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->careRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->careRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->careRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['care'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->careRepository->applyJoins();
            $data = $this->careRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->careRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->careRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->careRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['care'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->careRepository->applyJoins()
                ->where('his_care.id', $id);
            $data = $this->careRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->careRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['care'], $e);
        }
    }

    // public function createCare($request)
    // {
    //     try {
    //         $data = $this->careRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->careName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertCareIndex($data, $this->params->careName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['care'], $e);
    //     }
    // }

    // public function updateCare($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->careRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->careRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->careName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertCareIndex($data, $this->params->careName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['care'], $e);
    //     }
    // }

    // public function deleteCare($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->careRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->careRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->careName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->careName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['care'], $e);
    //     }
    // }
}
