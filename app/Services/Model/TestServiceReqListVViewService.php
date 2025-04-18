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
            $data = $this->testServiceReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->testServiceReqListVViewRepository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->testServiceReqListVViewRepository->applyToTimeFilter($data, $this->params->toTime);
            $data = $this->testServiceReqListVViewRepository->applyTreatmentType01IdFilter($data);
            $data = $this->testServiceReqListVViewRepository->applyTreatmentType01Filter($data, $this->params->isNoExcute, $this->params->isSpecimen, $this->params->cursorPaginate);
            $data = $this->testServiceReqListVViewRepository->applyExecuteDepartmentCodeFilter($data, $this->params->executeDepartmentCode);
            // $data = $this->testServiceReqListVViewRepository->applyIsNoExcuteFilter($data, $this->params->isNoExcute);
            // $data = $this->testServiceReqListVViewRepository->applyIsSpecimenFilter($data, $this->params->isSpecimen);
            if($this->params->start == 0){
                // $count = $data->count();
                $count = null;
            }else{
                $count = null;
            }
            $data = $this->testServiceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->testServiceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->testServiceReqListVViewRepository->applyJoins();
        // $data = $this->testServiceReqListVViewRepository->applyWith($data);
        if($this->params->treatmentCode || $this->params->patientCode){
            if($this->params->treatmentCode){
                $data = $this->testServiceReqListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            }
            if($this->params->patientCode){
                $data = $this->testServiceReqListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
            }
            if($this->params->patientPhone){
                $data = $this->testServiceReqListVViewRepository->applyPatientPhoneFilter($data, $this->params->patientPhone);
            }
        }else{
            $data = $this->testServiceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->testServiceReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->testServiceReqListVViewRepository->applyStatusFilter($data, $this->params->status);
            $data = $this->testServiceReqListVViewRepository->applyFromTimeFilter($data, $this->params->fromTime);
            $data = $this->testServiceReqListVViewRepository->applyToTimeFilter($data, $this->params->toTime);
            $data = $this->testServiceReqListVViewRepository->applyTreatmentType01IdFilter($data);
            $data = $this->testServiceReqListVViewRepository->applyTreatmentType01Filter($data, $this->params->isNoExcute, $this->params->isSpecimen, $this->params->cursorPaginate);
            $data = $this->testServiceReqListVViewRepository->applyExecuteDepartmentCodeFilter($data, $this->params->executeDepartmentCode);
        }
        // $data = $this->testServiceReqListVViewRepository->applyIsNoExcuteFilter($data, $this->params->isNoExcute);
        // $data = $this->testServiceReqListVViewRepository->applyIsSpecimenFilter($data, $this->params->isSpecimen);
        // if($this->params->start == 0){
        //     // $count = $data->count();
        //     $count = null;
        // }else{
        //     $count = null;
        // }
        $data = $this->testServiceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->testServiceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
        // if ($this->params->getAll) {
        //     $data = $data->filter(function ($item) {
        //         // Kiểm tra điều kiện isSpecimen
        //         $isSpecimenCondition = $this->params->isSpecimen ? 
        //             collect($item['test_service_type_list'])->contains('isSpecimen', '1') :
        //             collect($item['test_service_type_list'])->contains(function ($testServiceType) {
        //                 return $testServiceType['isSpecimen'] === "0" || $testServiceType['isSpecimen'] === "";
        //             });
        
        //         // Kiểm tra điều kiện isNoExecute
        //         $isNoExecuteCondition = $this->params->isNoExcute ? 
        //             collect($item['test_service_type_list'])->contains('isNoExecute', '1') :
        //             collect($item['test_service_type_list'])->contains(function ($testServiceType) {
        //                 return $testServiceType['isNoExecute'] === "0" || $testServiceType['isNoExecute'] === "";
        //             });
        
        //         // Trả về true nếu cả hai điều kiện đều thỏa mãn
        //         return $isSpecimenCondition && $isNoExecuteCondition;
        //     });
        // }            
        // Đếm sau khi đã tải tất cả bản ghi vào bộ nhớ
        if($this->params->getAll){
            $count = $data->count();
        }else{
            $count = null;
        }
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->testServiceReqListVViewRepository->applyJoins()
        ->where('id', $id);
    $data = $this->testServiceReqListVViewRepository->applyWith($data);
    $data = $this->testServiceReqListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->testServiceReqListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['test_service_req_list_v_view'], $e);
        }
    }

    public function handleViewNoLogin()
    {
        try {
            $data = [];
            $count = null;
            if($this->params->treatmentCode || $this->params->patientCode){
                $data = $this->testServiceReqListVViewRepository->applyJoins();
                if($this->params->treatmentCode){
                    $data = $this->testServiceReqListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
                }
                if($this->params->patientCode){
                    $data = $this->testServiceReqListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
                }
                $data = $this->testServiceReqListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->testServiceReqListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit, $this->params->cursorPaginate, $this->params->lastId);
            }
            return ['data' => $data, 'count' => $count];
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
