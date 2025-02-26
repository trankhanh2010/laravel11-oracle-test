<?php

namespace App\Repositories;

use App\Http\Resources\DB\TestServiceReqListVViewResource;
use App\Http\Resources\DB\TreatmentFeeListVViewResource;
use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Department;
use App\Models\HIS\SereServ;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\TreatmentType;
use App\Models\View\TreatmentFeeListVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TreatmentFeeListVViewRepository
{
    protected $serviceReqTypeXNId;
    protected $treatmentType01Id;
    protected $treatmentFeeListVView;
    protected $serviceReqType;
    protected $treatmentType;
    protected $serviceReq;
    protected $sereServ;
    protected $department;
    public function __construct(
        TreatmentFeeListVView $treatmentFeeListVView,
        ServiceReqType $serviceReqType,
        TreatmentType $treatmentType,
        ServiceReq $serviceReq,
        SereServ $sereServ,
        Department $department,
    ) {
        $this->treatmentFeeListVView = $treatmentFeeListVView;
        $this->serviceReqType = $serviceReqType;
        $this->treatmentType = $treatmentType;
        $this->serviceReq = $serviceReq;
        $this->sereServ = $sereServ;
        $this->department = $department;
    }

    public function applyJoins()
    {

        $query = $this->treatmentFeeListVView;
        return $query
            ->select('v_his_treatment_fee_list.*');
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
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee_list.service_req_code'), 'like', $keyword . '%');
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
                $query->where('IN_TIME', '>=', $param);
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
                $query->where('IN_TIME', '<=', $param);
            });
        }
        return $query;
    }
    public function applyChuaRaVienChuaKhoaVienPhiFilter($query)
    {
            $query->where(function ($query) {
                $query->whereNull('fee_lock_time')
                ->whereNull('treatment_end_type_id');
            });
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_treatment_fee_list.' . $key, $item);
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
            $data = TreatmentFeeListVViewResource::collection($data);
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
        return $this->treatmentFeeListVView->find($id);
    }
    //
}
