<?php

namespace App\Services\Model;

use App\DTOs\DhstDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Dhst\InsertDhstIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DhstRepository;

class DhstService
{
    protected $dhstRepository;
    protected $params;
    public function __construct(DhstRepository $dhstRepository)
    {
        $this->dhstRepository = $dhstRepository;
    }
    public function withParams(DhstDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->dhstRepository->applyJoins();
            $data = $this->dhstRepository->applyWith($data);
            $data = $this->dhstRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->dhstRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->dhstRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $count = $data->count();
            $data = $this->dhstRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->dhstRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dhst'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->dhstRepository->applyJoins();
        $data = $this->dhstRepository->applyWith($data);
        $data = $this->dhstRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->dhstRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $count = $data->count();
        $data = $this->dhstRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->dhstRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->dhstRepository->applyJoins()
        ->where('his_dhst.id', $id);
    $data = $this->dhstRepository->applyWith($data);
    $data = $this->dhstRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->dhstRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dhst'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['dhst'], $e);
        }
    }

    // public function createDhst($request)
    // {
    //     try {
    //         $data = $this->dhstRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->dhstName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDhstIndex($data, $this->params->dhstName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['dhst'], $e);
    //     }
    // }

    // public function updateDhst($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->dhstRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->dhstRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->dhstName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDhstIndex($data, $this->params->dhstName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['dhst'], $e);
    //     }
    // }

    // public function deleteDhst($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->dhstRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->dhstRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->dhstName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->dhstName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['dhst'], $e);
    //     }
    // }
}
