<?php

namespace App\Services\Model;

use App\DTOs\TestIndexDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestIndex\InsertTestIndexIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestIndexRepository;

class TestIndexService 
{
    protected $testIndexRepository;
    protected $params;
    public function __construct(TestIndexRepository $testIndexRepository)
    {
        $this->testIndexRepository = $testIndexRepository;
    }
    public function withParams(TestIndexDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testIndexRepository->applyJoins();
            $data = $this->testIndexRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->testIndexRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testIndexRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->testIndexName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->testIndexRepository->applyJoins();
                $data = $this->testIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->testIndexRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testIndexRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->testIndexName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->testIndexRepository->applyJoins()
                    ->where('his_test_index.id', $id);
                $data = $this->testIndexRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index'], $e);
        }
    }
    public function createTestIndex($request)
    {
        try {
            $data = $this->testIndexRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexIndex($data, $this->params->testIndexName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index'], $e);
        }
    }

    public function updateTestIndex($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexIndex($data, $this->params->testIndexName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index'], $e);
        }
    }

    public function deleteTestIndex($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->testIndexName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index'], $e);
        }
    }
}
