<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Patient extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_patient';
    protected $fillable = [

    ];
    public function dich_vu_gan_nhat()
    {
        return $this->hasOne(SereServ::class, 'tdl_patient_id')
            ->leftJoin('his_service_req', 'his_service_req.id', '=', 'his_sere_serv.service_req_id')
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'his_service_req.service_req_type_id')
            ->leftJoin('his_service_req_stt', 'his_service_req_stt.id', '=', 'his_service_req.service_req_stt_id')
            ->leftJoin('v_his_room execute_room','execute_room.id', '=', 'his_service_req.execute_room_id')
            ->select([
                'his_sere_serv.id as key',
                'his_service_req.tdl_patient_id', 
                'his_service_req.intruction_time',
                'his_sere_serv.tdl_service_name',
                'execute_room.room_name as execute_room_name',
                ])
            ->where(function ($q) {
                $q->where('his_sere_serv.is_no_execute', 0)
                ->orWhereNull('his_sere_serv.is_no_execute');
            })
            ->where('his_sere_serv.is_delete', 0)
            ->where('his_service_req.is_main_exam', 1)
            ->where('his_service_req.is_delete', 0)
            ->where('his_service_req_stt.service_req_stt_code', '03') // Lấy của mấy cái đã hoàn thành
            ->where(function ($query) {
                $query->where('his_service_req.is_no_execute', 0)
                    ->orWhereNull('his_service_req.is_no_execute');
            })
            ->where('his_service_req_type.service_req_type_code', 'KH')
            ->orderByDesc('his_service_req.intruction_time');
    }

    public function cac_dich_vu_da_dung()
    {
        return $this->hasMany(SereServ::class, 'tdl_patient_id')
            ->leftJoin('his_service_req', 'his_service_req.id', '=', 'his_sere_serv.service_req_id')
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'his_service_req.service_req_type_id')
            ->leftJoin('his_service_req_stt', 'his_service_req_stt.id', '=', 'his_service_req.service_req_stt_id')
            ->leftJoin('v_his_room execute_room','execute_room.id', '=', 'his_service_req.execute_room_id')
            ->select([
                'his_sere_serv.id as key',
                'his_service_req.tdl_patient_id', 
                'his_service_req.intruction_time',
                'his_sere_serv.tdl_service_name',
                'execute_room.room_name as execute_room_name',
                ])
            ->where(function ($q) {
                $q->where('his_sere_serv.is_no_execute', 0)
                ->orWhereNull('his_sere_serv.is_no_execute');
            })
            ->where('his_sere_serv.is_delete', 0)
            ->where('his_service_req.is_main_exam', 1)
            ->where('his_service_req.is_delete', 0)
            ->whereNotIn('his_service_req_stt.service_req_stt_code', ['01']) // Lấy của mấy cái khác trạng thái chưa thực hiện
            ->where(function ($query) {
                $query->where('his_service_req.is_no_execute', 0)
                    ->orWhereNull('his_service_req.is_no_execute');
            })
            ->where('his_service_req_type.service_req_type_code', 'KH')
            ->orderByDesc('his_service_req.intruction_time');
    }
    public function cac_dich_vu_da_dang_ky()
    {
        return $this->hasMany(SereServ::class, 'tdl_patient_id')
            ->leftJoin('his_service_req', 'his_service_req.id', '=', 'his_sere_serv.service_req_id')
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'his_service_req.service_req_type_id')
            ->leftJoin('his_service_req_stt', 'his_service_req_stt.id', '=', 'his_service_req.service_req_stt_id')
            ->leftJoin('v_his_room execute_room','execute_room.id', '=', 'his_service_req.execute_room_id')
            ->select([
                'his_sere_serv.id as key',
                'his_service_req.tdl_patient_id', 
                'his_service_req.intruction_time',
                'his_sere_serv.tdl_service_name',
                'execute_room.room_name as execute_room_name',
                'his_service_req.num_order',
                'his_sere_serv.amount', // SL
                'his_sere_serv.price', // Đơn giá
                'his_sere_serv.VIR_TOTAL_PRICE', // Thành tiền
                'his_sere_serv.VIR_TOTAL_HEIN_PRICE', // bh trả
                'his_sere_serv.VIR_TOTAL_PATIENT_PRICE', // bệnh nhân trả
                'his_sere_serv.VAT_RATIO', // % vat
                'his_sere_serv.DISCOUNT', // chiết khấu
                ])
            ->where(function ($q) {
                $q->where('his_sere_serv.is_no_execute', 0)
                ->orWhereNull('his_sere_serv.is_no_execute');
            })
            ->where('his_sere_serv.is_delete', 0)
            ->where('his_service_req.is_main_exam', 1)
            ->where('his_service_req.is_delete', 0)
            ->where('his_service_req_stt.service_req_stt_code', '01') // Lấy của mấy cái trạng thái chưa thực hiện
            ->where(function ($query) {
                $query->where('his_service_req.is_no_execute', 0)
                    ->orWhereNull('his_service_req.is_no_execute');
            })
            ->where('his_service_req_type.service_req_type_code', 'KH')
            ->orderBy('his_service_req.intruction_time');
    }
}
