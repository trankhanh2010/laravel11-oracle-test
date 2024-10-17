<?php

namespace App\Services\Model;

use App\DTOs\TestServiceReqListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestServiceReqListVView\InsertTestServiceReqListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestServiceReqListVViewRepository;

class TestServiceReqListVViewService
{
    protected $testServiceReqListVViewRepository;
    protected $params;
    public function __construct(TestServiceReqListVViewRepository $testServiceReqListVViewRepository)
    {
        $this->testServiceReqListVViewRepository = $testServiceReqListVViewRepository;
    }
    public function withParams(TestServiceReqListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testServiceReqListVViewRepository->applyJoins();
            $data = $this->testServiceReqListVViewRepository->applyWith($data);
            $data = $this->testServiceReqListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testServiceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->testServiceReqListVViewRepository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->testServiceReqListVViewRepository->applyToTimeFilter($data, $this->params->toTime);
            $data = $this->testServiceReqListVViewRepository->applyExecuteDepartmentCodeFilter($data, $this->params->executeDepartmentCode);
            $data = $this->testServiceReqListVViewRepository->applyIsConfirmNoExcuteFilter($data, $this->params->isConfirmNoExcute);
            $data = $this->testServiceReqListVViewRepository->applyIsSpecimenFilter($data, $this->params->isSpecimen);
            $count = $data->count();
            $data = $this->testServiceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testServiceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->testServiceReqListVViewRepository->applyJoins();
            $data = $this->testServiceReqListVViewRepository->applyWith($data);
            $data = $this->testServiceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->testServiceReqListVViewRepository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->testServiceReqListVViewRepository->applyToTimeFilter($data, $this->params->toTime);
            $data = $this->testServiceReqListVViewRepository->applyExecuteDepartmentCodeFilter($data, $this->params->executeDepartmentCode);
            $data = $this->testServiceReqListVViewRepository->applyIsConfirmNoExcuteFilter($data, $this->params->isConfirmNoExcute);
            $data = $this->testServiceReqListVViewRepository->applyIsSpecimenFilter($data, $this->params->isSpecimen);
            $count = $data->count();
            $data = $this->testServiceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testServiceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->testServiceReqListVViewRepository->applyJoins()
                ->where('v_his_test_service_req_list.id', $id);
            $data = $this->testServiceReqListVViewRepository->applyWith($data);
            $data = $this->testServiceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }

    // public function createTestServiceReqListVView($request)
    // {
    //     try {
    //         $data = $this->testServiceReqListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->testServiceReqListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTestServiceReqListVViewIndex($data, $this->params->testServiceReqListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
    //     }
    // }

    // public function updateTestServiceReqListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->testServiceReqListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->testServiceReqListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->testServiceReqListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTestServiceReqListVViewIndex($data, $this->params->testServiceReqListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
    //     }
    // }

    // public function deleteTestServiceReqListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->testServiceReqListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->testServiceReqListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->testServiceReqListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->testServiceReqListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
    //     }
    // }
}
