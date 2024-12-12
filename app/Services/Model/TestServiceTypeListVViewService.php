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
            $data = $this->testServiceTypeListVViewRepository->applyPatientIdFilter($data, $this->params->patientId);
            $count = $data->count();
            $data = $this->testServiceTypeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_type_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->testServiceTypeListVViewName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->testServiceTypeListVViewRepository->applyJoins();
                $data = $this->testServiceTypeListVViewRepository->applyPatientIdFilter($data, $this->params->patientId);
                $count = $data->count();
                $data = $this->testServiceTypeListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_type_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->testServiceTypeListVViewName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->testServiceTypeListVViewRepository->applyJoins()
                    ->where('v_his_test_service_type_list.id', $id);
                $data = $this->testServiceTypeListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_type_list_v_view'], $e);
        }
    }
}
