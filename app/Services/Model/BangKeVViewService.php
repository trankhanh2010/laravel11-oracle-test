<?php

namespace App\Services\Model;

use App\DTOs\BangKeVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BangKeVView\InsertBangKeVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BangKeVViewRepository;

class BangKeVViewService 
{
    protected $bangKeVViewRepository;
    protected $params;
    public function __construct(BangKeVViewRepository $bangKeVViewRepository)
    {
        $this->bangKeVViewRepository = $bangKeVViewRepository;
    }
    public function withParams(BangKeVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->bangKeVViewRepository->applyJoins();
        $data = $this->bangKeVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = $data->count();
        $data = $this->bangKeVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $data = $this->bangKeVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bangKeVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->bangKeVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bang_ke_v_view'], $e);
        }
    }
}
