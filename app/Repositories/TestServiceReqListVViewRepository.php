<?php

namespace App\Repositories;

use App\Http\Resources\DB\TestServiceReqListVViewResource;
use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Department;
use App\Models\HIS\SereServ;
use App\Models\HIS\ServiceReq;
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
    protected $serviceReq;
    protected $sereServ;
    protected $department;
    public function __construct(
        TestServiceReqListVView $testServiceReqListVView,
        ServiceReqType $serviceReqType,
        TreatmentType $treatmentType,
        ServiceReq $serviceReq,
        SereServ $sereServ,
        Department $department,
    ) {
        $this->testServiceReqListVView = $testServiceReqListVView;
        $this->serviceReqType = $serviceReqType;
        $this->treatmentType = $treatmentType;
        $this->serviceReq = $serviceReq;
        $this->sereServ = $sereServ;
        $this->department = $department;
    }

    public function applyJoins()
    {
        // $this->serviceReqTypeXNId = Cache::remember('service_req_type_XN_id', now()->addMinutes(10080), function () {
        //     $data =  $this->serviceReqType->where('service_req_type_code', 'XN')->get();
        //     return $data->value('id');
        // });
        $query = $this->testServiceReqListVView;
        return $query
            // ->where('service_req_type_id', $this->serviceReqTypeXNId)
            ->select('v_his_test_service_req_list.*');
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            // 'testServiceTypeList:service_req_id,is_specimen,is_no_execute,tdl_service_code,tdl_service_name',
            // 'testServiceTypeList.sereServBills:id,sere_serv_id,is_delete,bill_id,is_cancel',
            // 'testServiceTypeList.sereServDeposits:id,sere_serv_id,is_delete,deposit_id,is_cancel',
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
            $query->where('is_active', $isActive);
        }
        return $query;
    }
    public function applyTreatmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where('treatment_code', $code);
        }
        return $query;
    }
    public function applyPatientCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where('patient_code', $code);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where('is_delete', $isDelete);
        }
        return $query;
    }
    public function applyPatientPhoneFilter($query, $param)
    {
        if ($param !== null) {
            $query->where('patient_phone', $param);
        }
        return $query;
    }
    public function applyStatusFilter($query, $param)
    {
        switch($param){
            case 'ChuaKhoaVienPhi':
                $query->whereNull('fee_lock_time');
            break;
            case 'DaKhoaVienPhi':
                $query->whereNotNull('fee_lock_time');
            break;
            case 'DaKetThucDieuTriNhungChuaDuyetKhoaVienPhi':
                $query->where('is_pause', 1)
                ->whereNull('fee_lock_time');
            break;
            case 'ChuaKetThucDieuTri':
                $query->whereNull('is_pause');
            break;
            case 'BenhNhanBHYT':
                $query->where('patient_type_code', '01');
            break;
            case 'DaKhoaVienPhiNhungChuaDuyetBHYT':
                $query->whereNotNull('fee_lock_time')
                ->whereNotNull('hein_lock_time');
            break;
        };

        return $query;
    }
    public function applyFromTimeFilter($query, $param)
    {
        if ($param !== null) {
            // $param = $param - ($param % 1000000);
            return $query->where(function ($query) use ($param) {
                // $query->where(DB::connection('oracle_his')->raw('CREATE_TIME-MOD(CREATE_TIME,1000000)'), '>=', $param);
                // $query->where('vir_create_date', '>=', $param);
                $query->where('INTRUCTION_TIME', '>=', $param);
            });
        }
        return $query;
    }
    public function applyToTimeFilter($query, $param)
    {
        if ($param !== null) {
            // $param = $param - ($param % 1000000);
            return $query->where(function ($query) use ($param) {
                // $query->where(DB::connection('oracle_his')->raw('CREATE_TIME-MOD(CREATE_TIME,1000000)'), '<=', $param);
                // $query->where('vir_create_date', '<=', $param);
                $query->where('INTRUCTION_TIME', '<=', $param);
            });
        }
        return $query;
    }
    public function applyExecuteDepartmentCodeFilter($query, $param)
    {
        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                $query->where("execute_department_code", $param);
            });
        }

        // if ($param !== null) {
        //     $id = Cache::remember('id_department_code_' . $param, now()->addMinutes(10080), function () use ($param) {
        //         $data =  $this->department->where('department_code', $param)->first()->id ?? 0;
        //         return $data;
        //     });
        //     return $query->where(function ($query) use ($id) {
        //         $query->where("v_his_test_service_req_list.execute_department_id", $id);
        //     });
        // }
        return $query;
    }
    public function applyTreatmentType01IdFilter($query)
    {
        // $this->treatmentType01Id = Cache::remember('treatment_type_01_id', now()->addMinutes(10080), function () {
        //     $data =  $this->treatmentType->where('treatment_type_code', '01')->get();
        //     return $data->value('id');
        // });
        // $query = $query->where(function ($query) {
        //     $query->where("v_his_test_service_req_list.treatment_type_id", $this->treatmentType01Id);
        // });



        // Kiểm trong View 
        return $query;
    }
    public function applyTreatmentType01Filter($query, $isNoExecute, $isSpecimen, $cursorPaginate)
    {
        // - Nếu treatment_type_id=1 thì phải thỏa một trong các điều kiện sau:
        // + Tồn tại dữ liệu his_sere_sere_bill (với is_cancel khác 1) với is_delete=0 tương ứng các sere_serv_id (is_delete=0 và is_no_execute is null) của service_req_id check thì trả tất cả dữ liệu thuộc his_service_req_id đó ngược lại bỏ qua không trả dữ liệu thuộc y lệnh đó.
        // + Xử lý his_sere_sere_deposit (với is_cancel khác 1) tương tự his_sere_sere_bill
        // + Tính tổng tiền cần thanh toán của treatment_id, với tiền cần thanh toán là cột vir_total_patient_price table his_sere_serv
        //   Tính tổng tiền bệnh nhân thanh toán, qua table his_transaction, với transaction_type_id in (2,4) là phiếu hoàn tiền, còn lại là các giá trị khác là thu tiền. phiếu thu là + phiếu kia là -
        //   Nếu tất cả giá all_vir_total_price_zero đều là 0 hết thì k cần check 
        //   Trong sere_serv nếu IS_NO_PAY thì k cần check
        //   Nếu tổng tiền bệnh nhân thanh toán - tổng tiền cần thanh toán là số dương thì trả tất cả dữ liệu thỏa điều kiện lọc
        // if($cursorPaginate){
            $query = $query->where(function ($query) use ($isNoExecute, $isSpecimen) {
                $query
                    // Loại bỏ các bản ghi total sai
                    // ->where(function ($query) use ($isNoExecute, $isSpecimen) {
                    //     $query->whereDoesntHave('testServiceTypeList', function ($query) {
                    //         // Kiểm tra có ít nhất một bản ghi không có cả sereServBills và sereServDeposits
                    //         $query->whereDoesntHave('sereServBills')
                    //             ->whereDoesntHave('sereServDeposits');
                    //     });
                    // })
                    ->whereHas('testServiceTypeList', function ($query) use ($isNoExecute, $isSpecimen) {
                        // Điều kiện is_delete = 0 và is_no_execute = null trong testServiceTypeList và IS_NO_PAY khác 1
                        $query->where(function ($query) use ($isNoExecute, $isSpecimen) {
                            $query = $this->applyIsNoExcuteFilter($query, $isNoExecute);
                            $query = $this->applyIsSpecimenFilter($query, $isSpecimen);
                            // Kiểm tra tồn tại bản ghi trong sereServDeposits hoặc sereServBills với is_delete = 0
                            // $query->whereHas('sereServDeposits', function ($query) {
                            //     $query->where('is_delete', 0)
                            //         ->where(function ($query) {
                            //             $query->where('is_cancel', 0)
                            //                 ->orWhereNull('is_cancel');
                            //         });
                            // })
                            //     ->orWhereHas('sereServBills', function ($query) {
                            //         $query->where('is_delete', 0)
                            //             ->where(function ($query) {
                            //                 $query->where('is_cancel', 0)
                            //                     ->orWhereNull('is_cancel');
                            //             });
                            //     })
                            ;
                        });
                    })
                    // // Nếu tất cả giá all_vir_total_price_zero đều là 0 hết thì k cần check 
                    // ->orWhere(function ($query) {
                    //     $query = $query->where('all_vir_total_price_zero', 1);
                    // })
                    // // Nếu trả đủ tiền
                    // ->orWhere(function ($query) {
                    //     $query = $this->applyCheckSufficientPaymentFilter($query);
                    // })
                ;
            });
    
        // }
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
        // if ($param !== null) {
        //     return $query->where(function ($query) use ($param) {
        //         if ($param) {
        //             // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 1
        //             $query->whereHas('testServiceTypeList', function ($query) {
        //                 $query->where('is_specimen', 1);
        //             });
        //         } else {
        //             // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 0 hoặc null
        //             $query->whereHas('testServiceTypeList', function ($query) {
        //                 $query->where('is_specimen', 0)
        //                     ->orWhereNull('is_specimen');
        //             });
        //         }
        //     });
        // }

        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                if ($param) {
                    $query->where('is_specimen', 1);
                } else {
                    $query->where('is_specimen', 0)
                        ->orWhereNull('is_specimen');
                }
            });
        }
        return $query;
    }
    public function applyIsNoExcuteFilter($query, $param)
    {
        // if ($param !== null) {
        //     return $query->where(function ($query) use ($param) {
        //         if ($param) {
        //             // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 1
        //             $query->whereHas('testServiceTypeList', function ($query) {
        //                 $query->where('is_no_execute', 1);
        //             });
        //         } else {
        //             // Tất cả bản ghi trong testServiceTypeList phải có is_no_execute = 0 hoặc null
        //             $query->whereHas('testServiceTypeList', function ($query) {
        //                 $query->where('is_no_execute', 0)
        //                     ->orWhereNull('is_no_execute');
        //             });
        //         }
        //     });
        // }

        if ($param !== null) {
            return $query->where(function ($query) use ($param) {
                if ($param) {
                    $query->where('is_no_execute', 1);
                } else {
                    $query->where('is_no_execute', 0)
                        ->orWhereNull('is_no_execute');
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
    public function fetchData($query, $getAll, $start, $limit, $cursorPaginate, $lastId)
    {
        if ($cursorPaginate) {
            $sql = $query->toSql();
            $bindings = $query->getBindings();

            // Thêm các giá trị cần bind vào mảng $bindings
            $bindings[] = $limit + $start; // Giá trị ROWNUM <= ($limit + $start)
            $bindings[] = $lastId;         // Giá trị ID > $lastId
            $bindings[] = $start;          // Giá trị rnum > $start
        
            $fullSql = 'SELECT * FROM (
                            SELECT a.*, ROWNUM rnum 
                            FROM (' . $sql . ') a 
                            WHERE ROWNUM <= ?
                              AND ID > ?
                        ) WHERE rnum > ?';
            // $fullSql = 'SELECT * FROM (
            //     SELECT a.*, ROWNUM rnum 
            //     FROM (' . $sql . ') a 
            //     WHERE ROWNUM <= '.($limit + $start).'
            //       AND ID > ?
            // ) WHERE rnum > '.$start;
        
            // Thực hiện truy vấn với các bindings
            $data = DB::connection('oracle_his')->select($fullSql, $bindings);
            $data = TestServiceReqListVViewResource::collection($data);
            return $data;
        }
        
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
    //
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
