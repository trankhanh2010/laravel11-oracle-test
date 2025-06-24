<?php

namespace App\Models\View;

use App\Models\HIS\Card;
use App\Models\HIS\SereServ;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReqListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'xa_v_his_service_req_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function sere_serv()
    {
        return $this->hasMany(SereServ::class, 'service_req_id', 'id');
    }
    public function chi_tiet_don()
    {
        return $this->hasMany(DonChiTietVView::class, 'service_req_id', 'id')
            ->where('xa_v_his_don_chi_tiet.is_delete', 0)
            ->orderBy('xa_v_his_don_chi_tiet.num_order');;
    }
    public function danh_sach_dich_vu_chi_dinh()
    {
        return $this->hasMany(SereServ::class, 'service_req_id', 'id')
            ->leftJoin('his_patient_type', 'his_patient_type.id', '=', 'his_sere_serv.patient_type_id')
            ->leftJoin('his_service_unit', 'his_service_unit.id', '=', 'his_sere_serv.tdl_service_unit_id')
            ->leftJoin('his_service', 'his_service.id', '=', 'his_sere_serv.service_id')
            ->leftJoin('his_pttt_group', 'his_pttt_group.id', '=', 'his_service.pttt_group_id')
            ->select([
                'his_sere_serv.id',
                'his_sere_serv.service_req_id',
                'his_sere_serv.tdl_service_code',
                'his_sere_serv.tdl_service_name',
                'his_patient_type.patient_type_code',
                'his_patient_type.patient_type_name',
                'his_sere_serv.amount',
                'his_service_unit.service_unit_code',
                'his_service_unit.service_unit_name',
                'his_pttt_group.pttt_group_code',
                'his_pttt_group.pttt_group_name',
            ]);
    }
    public function list_card()
    {
        return $this->hasMany(Card::class,  'patient_id', 'tdl_patient_id');
    }
}
