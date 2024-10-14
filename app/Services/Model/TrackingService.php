<?php

namespace App\Services\Model;

use App\DTOs\TrackingDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Tracking\InsertTrackingIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrackingRepository;

class TrackingService
{
    protected $debateVViewRepository;
    protected $params;
    public function __construct(TrackingRepository $debateVViewRepository)
    {
        $this->debateVViewRepository = $debateVViewRepository;
    }
    public function withParams(TrackingDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->debateVViewRepository->applyJoins();
            $data = $this->debateVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->debateVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->debateVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->debateVViewRepository->applyJoins();
            $data = $this->debateVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->debateVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->debateVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->debateVViewRepository->applyJoins()
                ->where('his_tracking.id', $id);
            $data = $this->debateVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }

    public function createTracking($request)
    {
        try {
            $data = $this->debateVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateVViewName));
            // Gọi event để thêm index vào elastic
            event(new InsertTrackingIndex($data, $this->params->debateVViewName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }

    public function updateTracking($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateVViewName));
            // Gọi event để thêm index vào elastic
            event(new InsertTrackingIndex($data, $this->params->debateVViewName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }

    public function deleteTracking($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->debateVViewRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->debateVViewRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->debateVViewName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->debateVViewName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }
}
