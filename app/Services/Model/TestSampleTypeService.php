<?php

namespace App\Services\Model;

use App\DTOs\TestSampleTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestSampleType\InsertTestSampleTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestSampleTypeRepository;
use Illuminate\Support\Facades\Redis;

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
            $cacheKey = $this->params->testSampleTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->testSampleTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->testSampleTypeRepository->applyJoins();
                $data = $this->testSampleTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->testSampleTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testSampleTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->testSampleTypeName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->testSampleTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->testSampleTypeRepository->applyJoins()
                    ->where('his_test_sample_type.id', $id);
                $data = $this->testSampleTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }
    public function createTestSampleType($request)
    {
        try {
            $data = $this->testSampleTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestSampleTypeIndex($data, $this->params->testSampleTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testSampleTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }

    public function updateTestSampleType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testSampleTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testSampleTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestSampleTypeIndex($data, $this->params->testSampleTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testSampleTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }

    public function deleteTestSampleType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testSampleTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testSampleTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->testSampleTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testSampleTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_sample_type'], $e);
        }
    }
}
