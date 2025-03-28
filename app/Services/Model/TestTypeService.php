<?php

namespace App\Services\Model;

use App\DTOs\TestTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestType\InsertTestTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestTypeRepository;
use Illuminate\Support\Facades\Redis;

class TestTypeService 
{
    protected $testTypeRepository;
    protected $params;
    public function __construct(TestTypeRepository $testTypeRepository)
    {
        $this->testTypeRepository = $testTypeRepository;
    }
    public function withParams(TestTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testTypeRepository->applyJoins();
            $data = $this->testTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->testTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->testTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->testTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->testTypeRepository->applyJoins();
                $data = $this->testTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->testTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->testTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->testTypeRepository->applyJoins()
                    ->where('his_test_type.id', $id);
                $data = $this->testTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_type'], $e);
        }
    }
    public function createTestType($request)
    {
        try {
            $data = $this->testTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestTypeIndex($data, $this->params->testTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_type'], $e);
        }
    }

    public function updateTestType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestTypeIndex($data, $this->params->testTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_type'], $e);
        }
    }

    public function deleteTestType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->testTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_type'], $e);
        }
    }
}
