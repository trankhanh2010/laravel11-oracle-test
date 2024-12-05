<?php

namespace App\Services\Model;

use App\DTOs\TestIndexGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestIndexGroup\InsertTestIndexGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestIndexGroupRepository;

class TestIndexGroupService 
{
    protected $testIndexGroupRepository;
    protected $params;
    public function __construct(TestIndexGroupRepository $testIndexGroupRepository)
    {
        $this->testIndexGroupRepository = $testIndexGroupRepository;
    }
    public function withParams(TestIndexGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testIndexGroupRepository->applyJoins();
            $data = $this->testIndexGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testIndexGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->testIndexGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testIndexGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->testIndexGroupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->testIndexGroupRepository->applyJoins();
                $data = $this->testIndexGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->testIndexGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testIndexGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->testIndexGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->testIndexGroupRepository->applyJoins()
                    ->where('his_test_index_group.id', $id);
                $data = $this->testIndexGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }

    public function createTestIndexGroup($request)
    {
        try {
            $data = $this->testIndexGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexGroupIndex($data, $this->params->testIndexGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }

    public function updateTestIndexGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertTestIndexGroupIndex($data, $this->params->testIndexGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }

    public function deleteTestIndexGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->testIndexGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->testIndexGroupRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->testIndexGroupName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->testIndexGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_index_group'], $e);
        }
    }
}
