<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceReqType;
use App\Models\View\TestServiceReqListVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestServiceReqListVViewRepository
{
    protected $serviceReqTypeXNId;
    protected $testServiceReqListVView;
    protected $serviceReqType;
    public function __construct(TestServiceReqListVView $testServiceReqListVView, ServiceReqType $serviceReqType)
    {
        $this->testServiceReqListVView = $testServiceReqListVView;
        $this->serviceReqType = $serviceReqType;
    }

    public function applyJoins()
    {
        $this->serviceReqTypeXNId = Cache::remember('service_req_type_XN_id', now()->addMinutes(10080), function () {
            $data =  $this->serviceReqType->where('service_req_type_code', 'XN')->get();
            return $data->value('id');
        });
        return $this->testServiceReqListVView
            ->where('service_req_type_id', $this->serviceReqTypeXNId)
            ->select(
                'v_his_test_service_req_list.*'
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith(){
        return [
            'testServiceTypeList:id,service_req_id,is_specimen,is_confirm_no_excute,tdl_service_code,tdl_service_name',
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.service_req_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.is_active'), $isActive);
        }
        return $query;
    }
    public function applyFromTimeFilter($query, $param)
    {
        if($param !== null){
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.create_time'), '>=', $param);
            });
        }
        return $query;
    }
    public function applyToTimeFilter($query, $param)
    {
        if($param !== null){
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.create_time'), '<=', $param);
            });
        }
        return $query;
    }
    public function applyExecuteDepartmentCodeFilter($query, $param)
    {
        if($param !== null){
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw("execute_department_code"), 'like', $param);
            });
        } return $query;
    }
    public function applyIsSpecimenFilter($query, $param)
    {
        if($param !== null){
            return $query->where(function ($query) use ($param) {
                $query->whereHas('testServiceTypeList', function ($query) use ($param) {
                    if($param){
                        $query->where('is_specimen', 1);
                    }else{
                        $query->where('is_specimen', 0)
                        ->orWhereNull('is_specimen');
                    }
                });
            });
        }
        return $query;
    }
    public function applyIsConfirmNoExcuteFilter($query, $param)
    {
        if($param !== null){
            return $query->where(function ($query) use ($param) {
                $query->whereHas('testServiceTypeList', function ($query) use ($param) {
                    if($param){
                        $query->where('is_confirm_no_excute', 1);
                    }else{
                        $query->where('is_confirm_no_excute', 0)
                        ->orWhereNull('is_confirm_no_excute');
                    }
                });
            });
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_test_service_req_list.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->testServiceReqListVView->find($id);
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('v_his_test_service_req_list.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_test_service_req_list.id');
            $maxId = $this->applyJoins()->max('v_his_test_service_req_list.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('test_service_req_list_v_view', 'v_his_test_service_req_list', $startId, $endId, $batchSize, $this->paramWith());
            }
        }
    }
}