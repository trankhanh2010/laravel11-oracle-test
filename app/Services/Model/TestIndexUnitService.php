<?php

namespace App\Services\Model;

use App\DTOs\TestIndexUnitDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestIndexUnit\InsertTestIndexUnitIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestIndexUnitRepository;

class TestIndexUnitService 
{
    protected $testIndexUnitRepository;
    protected $params;
    public function __construct(TestIndexUnitRepository $testIndexUnitRepository)
    {
        $this->testIndexUnitRepository = $testIndexUnitRepository;
    }
    public function withParams(TestIndexUnitDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testIndexUnitRepository->applyJoins();
            $data = $this->testIndexUnitRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testIndexUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->testIndexUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testIndexUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_unit'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->testIndexUnitName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->testIndexUnitRepository->applyJoins();
                $data = $this->testIndexUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->testIndexUnitRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testIndexUnitRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_unit'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->testIndexUnitName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->testIndexUnitRepository->applyJoins()
                    ->where('his_test_index_unit.id', $id);
                $data = $this->testIndexUnitRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_unit'], $e);
        }
    }
    public function createTestIndexUnit($request)
    {
        try {
            $data = $this->testIndexUnitRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexUnitIndex($data, $this->params->testIndexUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexUnitName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_unit'], $e);
        }
    }

    public function updateTestIndexUnit($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexUnitRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexUnitIndex($data, $this->params->testIndexUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexUnitName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_unit'], $e);
        }
    }

    public function deleteTestIndexUnit($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexUnitRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexUnitRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->testIndexUnitName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexUnitName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_unit'], $e);
        }
    }
}
