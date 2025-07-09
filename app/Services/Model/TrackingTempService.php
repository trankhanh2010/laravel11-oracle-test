<?php

namespace App\Services\Model;

use App\DTOs\TrackingTempDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TrackingTemp\InsertTrackingTempIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TrackingTempRepository;
use Illuminate\Support\Facades\Redis;

class TrackingTempService
{
    protected $trackingTempRepository;
    protected $params;
    public function __construct(TrackingTempRepository $trackingTempRepository)
    {
        $this->trackingTempRepository = $trackingTempRepository;
    }
    public function withParams(TrackingTempDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->trackingTempRepository->applyJoins();
            $data = $this->trackingTempRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->trackingTempRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->trackingTempRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->trackingTempRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_temp'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->trackingTempRepository->applyJoins();
        $data = $this->trackingTempRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->trackingTempRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->trackingTempRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseSelectByLoginname()
    {
        $data = $this->trackingTempRepository->applyJoins();
        $data = $this->trackingTempRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->trackingTempRepository->applySelectByLoginnameFilter($data, $this->params->currentLoginname, $this->params->roomId);
        $count = $data->count();
        $data = $this->trackingTempRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->trackingTempRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->trackingTempRepository->applyJoins()
            ->where('his_tracking_temp.id', $id);
        $data = $this->trackingTempRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAllDataFromDatabaseSelectByLoginname()
    {
        try {
            return $this->getAllDataFromDatabaseSelectByLoginname();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_temp'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {

            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_temp'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tracking_temp'], $e);
        }
    }
}
