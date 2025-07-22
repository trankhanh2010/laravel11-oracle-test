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
    public function lan_kham_gan_nhat()
    {
        return $this->hasOne(ServiceReq::class, 'tdl_patient_id')
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'his_service_req.service_req_type_id')
            ->leftJoin('v_his_room execute_room','execute_room.id', '=', 'his_service_req.execute_room_id')
            ->select([
                'his_service_req.tdl_patient_id', 
                'his_service_req.intruction_time',
                'execute_room.room_name as execute_room_name',
            ])
            ->addSelect(DB::connection('oracle_his')->raw('(
                select tdl_service_name 
                from his_sere_serv 
                where his_sere_serv.service_req_id = his_service_req.id 
                    and his_sere_serv.is_delete = 0 
                    and (his_sere_serv.is_no_execute is null or his_sere_serv.is_no_execute = 0)
            ) as tdl_service_name'))
            ->where('his_service_req.is_main_exam', 1)
            ->where('his_service_req.is_delete', 0)
            ->where(function ($query) {
                $query->where('his_service_req.is_no_execute', 0)
                    ->orWhereNull('his_service_req.is_no_execute');
            })
            ->where('his_service_req_type.service_req_type_code', 'KH')
            ->orderByDesc('his_service_req.intruction_time');
    }

    public function cac_lan_kham()
    {
        return $this->hasMany(ServiceReq::class, 'tdl_patient_id')
            ->leftJoin('his_service_req_type', 'his_service_req_type.id', '=', 'his_service_req.service_req_type_id')
            ->leftJoin('v_his_room execute_room','execute_room.id', '=', 'his_service_req.execute_room_id')
            ->select([
                'his_service_req.tdl_patient_id', 
                'his_service_req.intruction_time',
                'execute_room.room_name as execute_room_name',
                ])
            ->addSelect(DB::connection('oracle_his')->raw('(select tdl_service_name from his_sere_serv where his_sere_serv.service_req_id = his_service_req.id and his_sere_serv.is_delete = 0 and (his_sere_serv.is_no_execute is null or his_sere_serv.is_no_execute = 0)) as tdl_service_name'))
            ->addSelect(DB::connection('oracle_his')->raw('(select id               from his_sere_serv where his_sere_serv.service_req_id = his_service_req.id and his_sere_serv.is_delete = 0 and (his_sere_serv.is_no_execute is null or his_sere_serv.is_no_execute = 0)) as key'))
            ->where('his_service_req.is_main_exam', 1)
            ->where('his_service_req.is_delete', 0)
            ->where(function ($query) {
                $query->where('his_service_req.is_no_execute', 0)
                    ->orWhereNull('his_service_req.is_no_execute');
            })
            ->where('his_service_req_type.service_req_type_code', 'KH')
            ->orderByDesc('his_service_req.intruction_time');
    }
}
