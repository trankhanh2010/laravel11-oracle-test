<?php

namespace App\Services\Model;

use App\DTOs\TestServiceReqListVView2DTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestServiceReqListVView2\InsertTestServiceReqListVView2Index;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TestServiceReqListVView2Repository;

class TestServiceReqListVView2Service
{
    protected $testServiceReqListVView2Repository;
    protected $params;
    public function __construct(TestServiceReqListVView2Repository $testServiceReqListVView2Repository)
    {
        $this->testServiceReqListVView2Repository = $testServiceReqListVView2Repository;
    }
    public function withParams(TestServiceReqListVView2DTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->testServiceReqListVView2Repository->applyJoins();
            $data = $this->testServiceReqListVView2Repository->applyWith($data);
            $data = $this->testServiceReqListVView2Repository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->testServiceReqListVView2Repository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->testServiceReqListVView2Repository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->testServiceReqListVView2Repository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->testServiceReqListVView2Repository->applyToTimeFilter($data, $this->params->toTime);
            $data = $this->testServiceReqListVView2Repository->applyExecuteDepartmentCodeFilter($data, $this->params->executeDepartmentCode);
            $data = $this->testServiceReqListVView2Repository->applyIsNoExcuteFilter($data, $this->params->isNoExcute);
            $data = $this->testServiceReqListVView2Repository->applyIsSpecimenFilter($data, $this->params->isSpecimen);
            // $count = $data->count();
            $count = null;
            $data = $this->testServiceReqListVView2Repository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testServiceReqListVView2Repository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view_2'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->testServiceReqListVView2Repository->applyJoins();
        $data = $this->testServiceReqListVView2Repository->applyWith($data);
        $data = $this->testServiceReqListVView2Repository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->testServiceReqListVView2Repository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->testServiceReqListVView2Repository->applyFromTimeFilter($data, $this->params->fromTime);
        $data = $this->testServiceReqListVView2Repository->applyToTimeFilter($data, $this->params->toTime);
        $data = $this->testServiceReqListVView2Repository->applyTreatmentType01IdFilter($data);
        $data = $this->testServiceReqListVView2Repository->applyTreatmentType01Filter($data, $this->params->isNoExcute, $this->params->isSpecimen);
        $data = $this->testServiceReqListVView2Repository->applyExecuteDepartmentCodeFilter($data, $this->params->executeDepartmentCode);
        // $data = $this->testServiceReqListVViewRepository->applyIsNoExcuteFilter($data, $this->params->isNoExcute);
        // $data = $this->testServiceReqListVViewRepository->applyIsSpecimenFilter($data, $this->params->isSpecimen);
        if($this->params->start == 0){
            // $count = $data->count();
            $count = null;
        }else{
            $count = null;
        }
        $data = $this->testServiceReqListVView2Repository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->testServiceReqListVView2Repository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->testServiceReqListVView2Repository->applyJoins()
        ->where('v_his_test_service_req_list_2.id', $id);
    $data = $this->testServiceReqListVView2Repository->applyWith($data);
    $data = $this->testServiceReqListVView2Repository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->testServiceReqListVView2Repository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view_2'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view_2'], $e);
        }
    }

    // public function createTestServiceReqListVView2($request)
    // {
    //     try {
    //         $data = $this->testServiceReqListVView2Repository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->testServiceReqListVView2Name));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTestServiceReqListVView2Index($data, $this->params->testServiceReqListVView2Name));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
    //     }
    // }

    // public function updateTestServiceReqListVView2($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->testServiceReqListVView2Repository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->testServiceReqListVView2Repository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->testServiceReqListVView2Name));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTestServiceReqListVView2Index($data, $this->params->testServiceReqListVView2Name));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
    //     }
    // }

    // public function deleteTestServiceReqListVView2($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->testServiceReqListVView2Repository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->testServiceReqListVView2Repository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->testServiceReqListVView2Name));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->testServiceReqListVView2Name));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['debate_v_view'], $e);
    //     }
    // }
}
