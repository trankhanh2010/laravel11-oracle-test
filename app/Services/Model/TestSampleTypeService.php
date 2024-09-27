<?php

namespace App\Services\Model;

use App\DTOs\TestSampleTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestSampleType\InsertTestSampleTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestSampleTypeRepository;

class TestSampleTypeService 
{
    protected $testSampleTypeRepository;
    protected $params;
    public function __construct(TestSampleTypeRepository $testSampleTypeRepository)
    {
        $this->testSampleTypeRepository = $testSampleTypeRepository;
    }
    public function withParams(TestSampleTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testSampleTypeRepository->applyJoins();
            $data = $this->testSampleTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testSampleTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->testSampleTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testSampleTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->testSampleTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->testSampleTypeRepository->applyJoins();
                $data = $this->testSampleTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->testSampleTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testSampleTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->testSampleTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->testSampleTypeRepository->applyJoins()
                    ->where('his_test_sample_type.id', $id);
                $data = $this->testSampleTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }
}
