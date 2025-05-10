<?php

namespace App\Services\Model;

use App\DTOs\TestServiceTypeListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestServiceTypeListVView\InsertTestServiceTypeListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestServiceTypeListVViewRepository;

class TestServiceTypeListVViewService 
{
    protected $testServiceTypeListVViewRepository;
    protected $params;
    public function __construct(TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository)
    {
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
    }
    public function withParams(TestServiceTypeListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testServiceTypeListVViewRepository->applyJoins();
            $data = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $count = $data->count();
            $data = $this->testServiceTypeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            $data = $this->testServiceTypeListVViewRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_type_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->testServiceTypeListVViewRepository->applyJoins();
        $data = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $count = $data->count();
        $data = $this->testServiceTypeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $data = $this->testServiceTypeListVViewRepository->themTienKhiTamUngDV($data, $this->params->treatmentId);
        $data = $this->testServiceTypeListVViewRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->testServiceTypeListVViewRepository->applyJoins()
        ->where('v_his_test_service_type_list.id', $id);
    $data = $this->testServiceTypeListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $data->first();
return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_type_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_type_list_v_view'], $e);
        }
    }
}
