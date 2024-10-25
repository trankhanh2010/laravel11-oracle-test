<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Department;
use App\Models\HIS\ServiceReqStt;
use App\Models\HIS\ServiceReqType;
use App\Models\View\TestServiceReqListVView2;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestServiceReqListVView2Repository
{
    protected $serviceReqTypeXNId;
    protected $chuaXuLyId;
    protected $dangXuLyId;
    protected $hoanThanhId;
    protected $testServiceReqListVView2;
    protected $serviceReqType;
    protected $serviceReqStt;
    protected $department;
    public function __construct(TestServiceReqListVView2 $testServiceReqListVView2, ServiceReqType $serviceReqType, ServiceReqStt $serviceReqStt, Department $department)
    {
        $this->testServiceReqListVView2 = $testServiceReqListVView2;
        $this->serviceReqType = $serviceReqType;
        $this->serviceReqStt = $serviceReqStt;
        $this->department = $department;
    }

    public function applyJoins()
    {
        $this->serviceReqTypeXNId = Cache::remember('service_req_type_XN_id', now()->addMinutes(10080), function () {
            $data =  $this->serviceReqType->where('service_req_type_code', 'XN')->get();
            return $data->value('id');
        });
        return $this->testServiceReqListVView2
            ->where('service_req_type_id', $this->serviceReqTypeXNId)
            ->select(
                'v_his_test_service_req_list_2.*'
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'testServiceTypeList:id,service_req_id,is_specimen,is_no_execute,tdl_service_code,tdl_service_name',
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list_2.service_req_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list_2.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list_2.is_delete'), $isActive);
        }
        return $query;
    }
    public function applyFromTimeFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list_2.create_time'), '>=', $param);
            });
        }
        return $query;
    }
    public function applyToTimeFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list_2.create_time'), '<=', $param);
            });
        }
        return $query;
    }
    public function applyExecuteDepartmentCodeFilter($query, $param)
    {
        // $list = Cache::remember('list_id_department_code_department', now()->addMinutes(10080), function () {
        //     $data =  $this->department->pluck( 'id', 'department_code')->toArray();
        //     return $data;
        // });
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw("v_his_test_service_req_list_2.execute_department_code"), $param);
            });
        }
        return $query;
    }
    public function applyIsSpecimenFilter($query, $param)
    {
        $this->chuaXuLyId = Cache::remember('service_req_stt_chua_xu_ly_id', now()->addMinutes(10080), function () {
            $data = $this->serviceReqStt->where('service_req_stt_code', '01')->get();
            return $data->value('id');
        });
        $this->dangXuLyId = Cache::remember('service_req_stt_dang_xu_ly_id', now()->addMinutes(10080), function () {
            $data = $this->serviceReqStt->where('service_req_stt_code', '02')->get();
            return $data->value('id');
        });
        $this->hoanThanhId = Cache::remember('service_req_stt_hoan_thanh_id', now()->addMinutes(10080), function () {
            $data =  $this->serviceReqStt->where('service_req_stt_code', '03')->get();
            return $data->value('id');
        });
        if ($param !== null) {
            if ($param) {
                $query->where(function ($query) {
                    $query->where('service_req_stt_id', $this->dangXuLyId)
                          ->orWhere('service_req_stt_id', $this->hoanThanhId);
                });
            } else {
                $query->where('service_req_stt_id', $this->chuaXuLyId);
            }
        }
        return $query;
    }
    public function applyIsNoExcuteFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                if ($param) {
                    // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 1
                    $query->whereDoesntHave('testServiceTypeList', function ($query) {
                        $query->where('is_no_execute', '<>', 1)
                        ->orWhereNull('is_no_execute');
                    });
                } else {
                    // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 0 hoặc null
                    $query->whereDoesntHave('testServiceTypeList', function ($query) {
                        $query->where('is_no_execute', '<>', 0)
                              ->whereNotNull('is_no_execute');
                    });
                }
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
                    $query->orderBy('v_his_test_service_req_list_2.' . $key, $item);
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
        return $this->testServiceReqListVView2->find($id);
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('v_his_test_service_req_list_2.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_test_service_req_list_2.id');
            $maxId = $this->applyJoins()->max('v_his_test_service_req_list_2.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('test_service_req_list_v_view_2', 'v_his_test_service_req_list_2', $startId, $endId, $batchSize, $this->paramWith());
            }
        }
    }
}
