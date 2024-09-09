<?php

namespace App\Services\Model;

use App\DTOs\AreaDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Area\InsertAreaIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AreaRepository;

class AreaService 
{
    protected $areaRepository;
    protected $params;
    public function __construct(AreaRepository $areaRepository)
    {
        $this->areaRepository = $areaRepository;
    }
    public function withParams(AreaDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->areaRepository->applyJoins();
            $data = $this->areaRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->areaRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->areaRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->areaRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->areaName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->areaRepository->applyJoins();
                $data = $this->areaRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->areaRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->areaRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->areaName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->areaRepository->applyJoins()
                    ->where('his_area.id', $id);
                $data = $this->areaRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function createArea($request)
    {
        try {
            $data = $this->areaRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->areaName));
            // Gọi event để thêm index vào elastic
            event(new InsertAreaIndex($data, $this->params->areaName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function updateArea($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->areaRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->areaRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->areaName));
            // Gọi event để thêm index vào elastic
            event(new InsertAreaIndex($data, $this->params->areaName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }

    public function deleteArea($id)
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
            event(new DeleteCache($this->params->areaName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->areaName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['area'], $e);
        }
    }
}
