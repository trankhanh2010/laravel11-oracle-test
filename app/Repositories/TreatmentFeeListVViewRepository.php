<?php

namespace App\Repositories;

use App\Http\Resources\DB\TestServiceReqListVViewResource;
use App\Http\Resources\DB\TreatmentFeeListVViewResource;
use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Department;
use App\Models\HIS\SereServ;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\TreatmentEndType;
use App\Models\HIS\TreatmentLogType;
use App\Models\HIS\TreatmentType;
use App\Models\View\TreatmentFeeListVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

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
    protected $treatmentLogType;
    protected $treatmentEndType;
    protected $treatmentLogTypeKhoaIds;
    protected $treatmentLogTypeKhoaCodes;
    protected $treatmentEndTypeTronVienId;
    public function __construct(
        TreatmentFeeListVView $treatmentFeeListVView,
        ServiceReqType $serviceReqType,
        TreatmentType $treatmentType,
        ServiceReq $serviceReq,
        SereServ $sereServ,
        Department $department,
        TreatmentLogType $treatmentLogType,
        TreatmentEndType $treatmentEndType,
    ) {
        $this->treatmentFeeListVView = $treatmentFeeListVView;
        $this->serviceReqType = $serviceReqType;
        $this->treatmentType = $treatmentType;
        $this->serviceReq = $serviceReq;
        $this->sereServ = $sereServ;
        $this->department = $department;
        $this->treatmentLogType = $treatmentLogType;
        $this->treatmentEndType = $treatmentEndType;

        $cacheKey = 'treatment_log_type_khoa_ids';
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $this->treatmentLogTypeKhoaIds = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            return $this->treatmentLogType
            ->whereIn('treatment_log_type_code', ['01','03','05','07'])
            ->pluck('id') // Lấy tất cả thành mảng
            ->toArray();  // Chuyển về mảng thuần
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $this->treatmentLogTypeKhoaCodes = ['01','03','05','07'];

        $cacheKey = 'treatment_end_type_tron_vien_id';
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $this->treatmentEndTypeTronVienId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->treatmentEndType->where('treatment_end_type_code', 'TR')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);


    }

    public function applyJoins()
    {

        $query = $this->treatmentFeeListVView;
        return $query
            ->select();
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'services',
            'deposit_req_list_is_deposit',
            'deposit_req_list_is_not_deposit',
            'treatment_fee_detail' => function ($query) {
                $query->select(
                    'xa_v_his_treatment_fee_detail.*',
                    DB::connection('oracle_his')->raw('(total_deposit_amount - total_repay_amount - total_bill_transfer_amount - total_bill_fund - total_bill_exemption + total_bill_amount + locking_amount) as da_thu'),
                    DB::connection('oracle_his')->raw('(total_deposit_amount - total_service_deposit_amount) as tam_ung'),
                    DB::connection('oracle_his')->raw('(total_patient_price - (total_deposit_amount - total_repay_amount - total_bill_transfer_amount + total_bill_amount + locking_amount)) as fee')
                );
            },
            // 'services.sereServBills:id,sere_serv_id,is_delete,bill_id,is_cancel',
            // 'services.sereServDeposits:id,sere_serv_id,is_delete,deposit_id,is_cancel',
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        if ($keyword != null) {
            return $query->where(function ($query) use ($keyword) {
                $query->whereRaw("
                REGEXP_LIKE(
                    NLSSORT(patient_name, 'NLS_SORT=GENERIC_M_AI'),
                    NLSSORT(?, 'NLS_SORT=GENERIC_M_AI'),
                    'i'
                )
            ", [$keyword]);
            });
        }
        return $query;
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
        if ($code != null) {
            $query->where('treatment_code', $code);
        }
        return $query;
    }
    public function applyPatientCodeFilter($query, $code)
    {
        if ($code != null) {
            $query->where('patient_code', $code);
        }
        return $query;
    }
    public function applyEndDepartmentCodesFilter($query, $code)
    {
        if ($code != null) {
            $query->whereIn('end_department_code', $code);
        }
        return $query;
    }
    public function applyPatientTypeCodesFilter($query, $code)
    {
        if ($code != null) {
            $query->whereIn('patient_type_code', $code);
        }
        return $query;
    }
    public function applyTreatmentTypeCodesFilter($query, $code)
    {
        if ($code != null) {
            $query->whereIn('treatment_type_code', $code);
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
        // Bảng HIS_TREATMENT
        // is_active 0 - khóa viện phí
        // is_pause 1 - kết thúc điều trị, 0 | null - đang điều trị
        // is_lock_hein - 1 khóa BHYT
        switch($param){
            case 'chuaKhoaVienPhi':
                $query->where('is_active', 1);
            break;
            case 'daKhoaVienPhi':
                $query->where('is_active', 0);
            break;
            case 'daKetThucDieuTriNhungChuaDuyetKhoaVienPhi':
                $query->where(function ($q) {
                    $q->where('is_pause', 1)
                    ->where('is_active', 1);
                });
            break;
            case 'chuaKetThucDieuTri':
                $query->where(function ($q) {
                    $q->whereNull('is_pause')
                    ->orWhere('is_pause', 0);
                });            
            break;
            case 'benhNhanBHYT':
                $query->where('patient_type_code', '01');
            break;
            case 'daKhoaVienPhiNhungChuaDuyetBHYT':
                $query->where('is_active', 0)
                ->where('is_hein_approval',0);
            break;
            case 'daDuyetBHYTNhungChuaKhoaBHYT':
                $query->where('is_hein_approval',1)
                ->where(function ($q) {
                    $q->whereNull('is_lock_hein')
                    ->orWhere('is_lock_hein', 0);
                });
            break;
            case 'daKhoaBHYTNhungChuaThanhToan':
                $query->whereNotNull('is_lock_hein')
                ->where('co_thanh_toan',0);
            break;
        };



        return $query;
    }
    public function applyFromTimeFilter($query, $param)
    {
        if ($param !== null) {
            // $param = $param - ($param % 1000000);
            return $query->where(function ($query) use ($param) {
                // $query->where(('CREATE_TIME-MOD(CREATE_TIME,1000000)'), '>=', $param);
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
                // $query->where(('CREATE_TIME-MOD(CREATE_TIME,1000000)'), '<=', $param);
                // $query->where('vir_create_date', '<=', $param);
                $query->where('IN_TIME', '<=', $param);
            });
        }
        return $query;
    }
    public function applyOutTimeFromFilter($query, $param)
    {
        if ($param !== null) {
            // $param = $param - ($param % 1000000);
            return $query->where(function ($query) use ($param) {
                // $query->where(('CREATE_TIME-MOD(CREATE_TIME,1000000)'), '>=', $param);
                // $query->where('vir_create_date', '>=', $param);
                $query->where('OUT_TIME', '>=', $param);
            });
        }
        return $query;
    }
    public function applyOutTimeToFilter($query, $param)
    {
        if ($param !== null) {
            // $param = $param - ($param % 1000000);
            return $query->where(function ($query) use ($param) {
                // $query->where(('CREATE_TIME-MOD(CREATE_TIME,1000000)'), '<=', $param);
                // $query->where('vir_create_date', '<=', $param);
                $query->where('OUT_TIME', '<=', $param);
            });
        }
        return $query;
    }
    public function applyChuaRaVienChuaKhoaVienPhiFilter($query)
    {
            $query->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('is_active', 1);
                })
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
                    $query->orderBy('' . $key, $item);
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
