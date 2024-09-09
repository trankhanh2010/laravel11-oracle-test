<?php

namespace App\Services\Model;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Area\InsertAreaIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Repositories\AreaRepository;

class AreaService extends BaseApiCacheController
{
    protected $areaRepository;
    protected $request;
    public function __construct(Request $request, AreaRepository $areaRepository)
    {
        parent::__construct($request);
        $this->areaRepository = $areaRepository;
        $this->request = $request;
    }

    public function handleDataBaseSearch($keyword, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = $this->areaRepository->applyJoins();
            $data = $this->areaRepository->applyKeywordFilter($data, $keyword);
            $data = $this->areaRepository->applyIsActiveFilter($data, $isActive);
            $count = $data->count();
            $data = $this->areaRepository->applyOrdering($data, $orderBy, $orderByJoin);
            $data = $this->areaRepository->fetchData($data, $getAll, $start, $limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
    public function handleDataBaseGetAll($areaName, $isActive, $orderBy, $orderByJoin, $getAll, $start, $limit)
    {
        try {
            $data = Cache::remember($areaName . '_start_' . $this->start . '_limit_' . $this->limit . $this->orderByString . '_is_active_' . $this->isActive . '_get_all_' . $this->getAll, $this->time, function () use ($isActive, $orderBy, $orderByJoin, $getAll, $start, $limit) {
                $data = $this->areaRepository->applyJoins();
                $data = $this->areaRepository->applyIsActiveFilter($data, $isActive);
                $count = $data->count();
                $data = $this->areaRepository->applyOrdering($data, $orderBy, $orderByJoin);
                $data = $this->areaRepository->fetchData($data, $getAll, $start, $limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
    public function handleDataBaseGetWithId($areaName, $id, $isActive)
    {
        try {
            $data = Cache::remember($areaName . '_' . $id . '_is_active_' . $this->isActive, $this->time, function () use ($id, $isActive) {
                $data = $this->areaRepository->applyJoins()
                    ->where('his_area.id', $id);
                $data = $this->areaRepository->applyIsActiveFilter($data, $isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function createArea($request, $time, $appCreator, $appModifier)
    {
        try {
            $data = $this->areaRepository->create($request, $time, $appCreator, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->areaName));
            // Gọi event để thêm index vào elastic
            event(new InsertAreaIndex($data, $this->areaName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function updateArea($areaName, $id, $request, $time, $appModifier)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->areaRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->areaRepository->update($request, $data, $time, $appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($areaName));
            // Gọi event để thêm index vào elastic
            event(new InsertAreaIndex($data, $areaName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function deleteArea($areaName, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->areaRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->areaRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($areaName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $areaName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
}
