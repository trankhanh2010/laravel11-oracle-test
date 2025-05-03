<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\TransReq;
use App\Models\View\RoomVView;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class TransReqRepository
{
    protected $transReq;
    protected $roomVView;
    protected $roomThuNganId;
    public function __construct(
        TransReq $transReq,
        RoomVView $roomVView,
        )
    {
        $this->transReq = $transReq;
        $this->roomVView = $roomVView;

        $cacheKey = 'room_TCKT_TN_id';
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $this->roomThuNganId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->roomVView->where('room_code', 'TCKT_TN')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
    }

    public function applyJoins()
    {
        return $this->transReq
            ->select(
                'his_trans_req.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_trans_req.trans_req_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_trans_req.trans_req_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_trans_req.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_trans_req.' . $key, $item);
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
        return $this->transReq->find($id);
    }
    public function createTransReqQrVtbByNguoiDung($request, $appCreator, $appModifier){
        $data = $this->transReq::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => 'MOS_v2',
            'modifier' => 'MOS_v2',
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'amount' => $request->amount,
            'treatment_id' => $request->treatment_id,
            'trans_req_stt_id' => 1, // 1- Yeu cau; 2: Hoan thanh; 3: That bai va ket thuc; 4: Huy 
            'bank_json_data' => $request->bank_json_data,
            'trans_req_type' => 3, // 1: Yeu cau thanh toan theo tung y lenh (co gan dich vu); 2: Yeu cau thanh toan theo so tien con thieu (co gan voi dich vu); 3: Yeu cau thanh toan theo tong so tien con thieu (khong gan voi dich vu); 4: Yeu cau thanh toan theo phieu yeu cau tam ung; 5: Yeu cau thanh toan theo giao dich , của bệnh nhân dùng trans_req_type 3 của thu ngân mới là trans_req_type 5 
            'bank_message'  => $request->bank_message,
            'tdl_treatment_code' => $request->tdl_treatment_code,
            'tdl_patient_code' => $request->tdl_patient_code,
            'tdl_patient_name' => $request->tdl_patient_name,
            'request_room_id' => $this->roomThuNganId,
            'request_loginname'  => 'MOS_v2',
            'request_username' => 'MOS_v2',
            'expired_time' => $request->expired_time,
            'bank'  => $request->bank,
            'request_id'  => $request->request_id,
            'qr_text'  => $request->qr_text,
            'qr_time'  => $request->qr_time,
            'bank_trans_code'  => $request->bank_trans_code,
            'merchant_code'  => $request->merchant_code,
            'terminal_id'  => $request->terminal_id,
        ]);
        return $data;
    }
    public function createTransReqQrVtbByThuNgan($request, $appCreator, $time = 14400, $appModifier){
        $data = $this->transReq::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'amount' => $request->amount,
            'treatment_id' => $request->treatment_id,
            'trans_req_stt_id' => 1, // 1- Yeu cau; 2: Hoan thanh; 3: That bai va ket thuc; 4: Huy 
            'bank_json_data' => $request->bank_json_data,
            'trans_req_type' => 5, // 1: Yeu cau thanh toan theo tung y lenh (co gan dich vu); 2: Yeu cau thanh toan theo so tien con thieu (co gan voi dich vu); 3: Yeu cau thanh toan theo tong so tien con thieu (khong gan voi dich vu); 4: Yeu cau thanh toan theo phieu yeu cau tam ung; 5: Yeu cau thanh toan theo giao dich , của bệnh nhân dùng trans_req_type 3 của thu ngân mới là trans_req_type 5 
            'bank_message'  => $request->bank_message,
            'tdl_treatment_code' => $request->tdl_treatment_code,
            'tdl_patient_code' => $request->tdl_patient_code,
            'tdl_patient_name' => $request->tdl_patient_name,
            'request_room_id' => $this->roomThuNganId,
            'request_loginname'  => get_loginname_with_token($request->bearerToken(), $time),
            'request_username' => get_loginname_with_token($request->bearerToken(), $time),
            'expired_time' => $request->expired_time,
            'bank'  => $request->bank,
            'request_id'  => $request->request_id,
            'qr_text'  => $request->qr_text,
            'qr_time'  => $request->qr_time,
            'bank_trans_code'  => $request->bank_trans_code,
            'merchant_code'  => $request->merchant_code,
            'terminal_id'  => $request->terminal_id,
        ]);
        return $data;
    }
}