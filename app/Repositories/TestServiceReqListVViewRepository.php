<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\TreatmentType;
use App\Models\View\TestServiceReqListVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TestServiceReqListVViewRepository
{
    protected $serviceReqTypeXNId;
    protected $treatmentType01Id;
    protected $testServiceReqListVView;
    protected $serviceReqType;
    protected $treatmentType;
    public function __construct(TestServiceReqListVView $testServiceReqListVView, ServiceReqType $serviceReqType, TreatmentType $treatmentType)
    {
        $this->testServiceReqListVView = $testServiceReqListVView;
        $this->serviceReqType = $serviceReqType;
        $this->treatmentType = $treatmentType;
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
                'v_his_test_service_req_list.*',
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'testServiceTypeList:id,service_req_id,is_delete,is_no_pay,is_specimen,is_no_execute,tdl_service_code,tdl_service_name,vir_total_patient_price',
            'testServiceTypeList.sereServBills:id,sere_serv_id,is_delete,bill_id,is_cancel',
            'testServiceTypeList.sereServDeposits:id,sere_serv_id,is_delete,deposit_id,is_cancel',
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
    public function applyIsDeleteFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.is_delete'), $isActive);
        }
        return $query;
    }
    public function applyFromTimeFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.create_time'), '>=', $param);
            });
        }
        return $query;
    }
    public function applyToTimeFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw('v_his_test_service_req_list.create_time'), '<=', $param);
            });
        }
        return $query;
    }
    public function applyExecuteDepartmentCodeFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where(DB::connection('oracle_his')->raw("execute_department_code"), $param);
            });
        }
        return $query;
    }
    public function applyTreatmentType01IdFilter($query)
    {
        $this->treatmentType01Id = Cache::remember('treatment_type_01_id', now()->addMinutes(10080), function () {
            $data =  $this->treatmentType->where('treatment_type_code', '01')->get();
            return $data->value('id');
        });
        return $query->where(function ($query) {
            $query->where(DB::connection('oracle_his')->raw("v_his_test_service_req_list.treatment_type_id"), $this->treatmentType01Id);
        });
    }
    public function applyTreatmentType01Filter($query)
    {
        // - Nếu treatment_type_id=1 thì phải thỏa một trong các điều kiện sau:
        // + Tồn tại dữ liệu his_sere_sere_bill (với is_cancel khác 1) với is_delete=0 tương ứng các sere_serv_id (is_delete=0 và is_no_execute is null) của service_req_id check thì trả tất cả dữ liệu thuộc his_service_req_id đó ngược lại bỏ qua không trả dữ liệu thuộc y lệnh đó.
        // + Xử lý his_sere_sere_deposit (với is_cancel khác 1) tương tự his_sere_sere_bill
        // + Tính tổng tiền cần thanh toán của treatment_id, với tiền cần thanh toán là cột vir_total_patient_price table his_sere_serv
        //   Tính tổng tiền bệnh nhân thanh toán, qua table his_transaction, với transaction_type_id in (2,4) là phiếu hoàn tiền, còn lại là các giá trị khác là thu tiền. phiếu thu là + phiếu kia là -
        //   Nếu tất cả giá all_vir_total_price_zero đều là 0 hết thì k cần check 
        //   Trong sere_serv nếu IS_NO_PAY thì k cần check
        //   Nếu tổng tiền bệnh nhân thanh toán - tổng tiền cần thanh toán là số dương thì trả tất cả dữ liệu thỏa điều kiện lọc

        $query = $query->where(function ($query) {
            $query
                // Loại bỏ các bản ghi total sai
                ->where(function ($query) {
                    $query->whereDoesntHave('testServiceTypeList', function ($query) {
                        // Kiểm tra có ít nhất một bản ghi không có cả sereServBills và sereServDeposits
                        $query->whereDoesntHave('sereServBills')
                              ->whereDoesntHave('sereServDeposits');
                    })
                    ->orWhereDoesntHave('testServiceTypeList', function ($query) {
                        // Kiểm tra có ít nhất một bản ghi có ít nhất một trong hai quan hệ
                        $query->whereHas('sereServBills')
                              ->orWhereHas('sereServDeposits');
                    });
                })
                ->whereHas('testServiceTypeList', function ($query) {
                    // Điều kiện is_delete = 0 và is_no_execute = null trong testServiceTypeList và IS_NO_PAY khác 1
                    $query->where(function ($query) {
                        // Kiểm tra tồn tại bản ghi trong sereServDeposits hoặc sereServBills với is_delete = 0
                        $query->whereHas('sereServDeposits', function ($query) {
                            $query->where('is_delete', '0')
                                ->where(function ($query) {
                                    $query->where('is_cancel', '0')
                                        ->orWhereNull('is_cancel');
                                });
                        })
                            ->orWhereHas('sereServBills', function ($query) {
                                $query->where('is_delete', '0')
                                    ->where(function ($query) {
                                        $query->where('is_cancel', '0')
                                            ->orWhereNull('is_cancel');
                                    });
                            });
                    });
                })
                // Nếu tất cả giá all_vir_total_price_zero đều là 0 hết thì k cần check 
                ->orWhere(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('all_vir_total_price_zero'), 1);
                })
                // Nếu trả đủ tiền
                ->orWhere(function ($query) {
                    $query = $this->applyCheckSufficientPaymentFilter($query);
                });
        });
        return $query;
    }
    // Kiểm tra xem tổng tiền bệnh nhân thanh toán - tổng tiền cần thanh toán có lớn hơn = 0 không
    public function applyCheckSufficientPaymentFilter($query)
    {
        $query = $query->where(DB::connection('oracle_his')->raw('total_treatment_bill_amount - total_vir_total_patient_price'), '>=', 0);
        return $query;
    }
    public function applyIsSpecimenFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                if ($param) {
                    // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 1
                    $query->whereDoesntHave('testServiceTypeList', function ($query) {
                        $query->where('is_specimen', '<>', 1)
                            ->orWhereNull('is_specimen');
                    });
                } else {
                    // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 0 hoặc null
                    $query->whereDoesntHave('testServiceTypeList', function ($query) {
                        $query->where('is_specimen', '<>', 0)
                            ->whereNotNull('is_specimen');
                    });
                }
            });
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
