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
    protected $trackingRepository;
    protected $params;
    public function __construct(TrackingRepository $trackingRepository)
    {
        $this->trackingRepository = $trackingRepository;
    }
    public function withParams(TrackingDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->trackingRepository->applyJoins();
            $data = $this->trackingRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->trackingRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->trackingRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->trackingRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->trackingRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->trackingRepository->applyJoins();
            $data = $this->trackingRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->trackingRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->trackingRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->trackingRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->trackingRepository->applyJoins()
                ->where('his_tracking.id', $id);
            $data = $this->trackingRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }

    public function createTracking($request)
    {
        try {
            $data = $this->trackingRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để xóa cache
            event(new DeleteCache($this->params->trackingName));
            // Gọi event để thêm index vào elastic
            event(new InsertTrackingIndex($data, $this->params->trackingName));
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
        $data = $this->trackingRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->trackingRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTrackingIndex($data, $this->params->trackingName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->trackingName));
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
        $data = $this->trackingRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->trackingRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->trackingName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->trackingName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking'], $e);
        }
    }
}
