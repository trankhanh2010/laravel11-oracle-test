<?php

namespace App\Models\View;

use App\Models\HIS\Card;
use App\Models\HIS\ExpMest;
use App\Models\HIS\SereServ;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ServiceReqListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'xa_v_his_service_req_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    protected $hidden = [
        'tdl_service_name_sort', // Tránh trả ra JSON -- trường này chỉ dùng để sắp xếp trong lấy lấy data danh sách y lệnh cho thêm tờ điều trị
        'sort_num_order',
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
            ->leftJoin('his_service_unit convert', 'convert.id', '=', 'his_service_unit.convert_id')
            ->leftJoin('his_service', 'his_service.id', '=', 'his_sere_serv.service_id')
            ->leftJoin('his_service_type', 'his_service_type.id', '=', 'his_sere_serv.tdl_service_type_id')
            ->leftJoin('his_pttt_group', 'his_pttt_group.id', '=', 'his_service.pttt_group_id')
            ->leftJoin('his_medicine_type', 'his_medicine_type.service_id', '=', 'his_service.id')
            // ->leftJoin('XA_V_HIS_DON', function ($join) {
            //     $join->on('XA_V_HIS_DON.service_req_id', '=', 'his_sere_serv.service_req_id')
            //         ->whereColumn('XA_V_HIS_DON.m_type_id', '=', 'his_medicine_type.id')
            //         ->whereColumn('XA_V_HIS_DON.is_delete', '=', 0);
            // })

            ->where('his_sere_serv.is_delete',0)
            ->whereNotIn('his_service_type.service_type_code', ['TH', 'VT']) // k lấy service thuốc vật tư vì đã lấy ở dưới danh_sach_don

            ->select([
                'his_sere_serv.id',
                // 'XA_V_HIS_DON.m_type',
                DB::connection('oracle_his')->raw("NULL as m_type"),
                'his_service_type.service_type_code',
                'his_service_type.service_type_name',
                'his_sere_serv.service_req_id',
                'his_sere_serv.tdl_service_code',
                'his_sere_serv.tdl_service_name',
                'his_patient_type.patient_type_code',
                'his_patient_type.patient_type_name',
                'his_sere_serv.amount',
                'his_service_unit.service_unit_code',
                'his_service_unit.service_unit_name',
                'his_service_unit.convert_ratio',
                'convert.service_unit_code as convert_code',
                'convert.service_unit_name as convert_name',
                'his_pttt_group.pttt_group_code',
                'his_pttt_group.pttt_group_name',
                // 'XA_V_HIS_DON.speed',
                // 'XA_V_HIS_DON.tutorial',
                DB::connection('oracle_his')->raw("NULL as speed"),
                DB::connection('oracle_his')->raw("NULL as tutorial"),
            ])
            ->orderByRaw("NLSSORT(service_name, 'NLS_SORT = Vietnamese')"); // sắp theo chữ cái tiếng việt
    }
    public function danh_sach_don()
    {
        return $this->hasMany(DonVView::class, 'service_req_id', 'id')
            ->leftJoin('his_service', 'his_service.id', '=', 'XA_V_HIS_DON.service_id')
            ->leftJoin('his_service_unit', 'his_service_unit.id', '=', 'his_service.service_unit_id')
            ->leftJoin('his_service_unit convert', 'convert.id', '=', 'his_service_unit.convert_id')
            ->leftJoin('his_service_type', 'his_service_type.id', '=', 'his_service.service_type_id')
            ->leftJoin('his_pttt_group', 'his_pttt_group.id', '=', 'his_service.pttt_group_id')
            ->leftJoin('his_medicine_type', 'his_medicine_type.service_id', '=', 'his_service.id')

            ->where('XA_V_HIS_DON.is_delete',0)

            ->select([
                'XA_V_HIS_DON.id',
                'XA_V_HIS_DON.m_type',
                'his_service_type.service_type_code',
                'his_service_type.service_type_name',
                'XA_V_HIS_DON.service_req_id',
                'his_service.service_code as tdl_service_code',
                'his_service.service_name as tdl_service_name',
                DB::connection('oracle_his')->raw("CAST(NULL AS VARCHAR2(100)) as patient_type_code"),
                DB::connection('oracle_his')->raw("CAST(NULL AS VARCHAR2(100)) as patient_type_name"),
                'XA_V_HIS_DON.amount',
                'his_service_unit.service_unit_code',
                'his_service_unit.service_unit_name',
                'his_service_unit.convert_ratio',
                'convert.service_unit_code as convert_code',
                'convert.service_unit_name as convert_name',
                'his_pttt_group.pttt_group_code',
                'his_pttt_group.pttt_group_name',
                'XA_V_HIS_DON.speed',
                'XA_V_HIS_DON.tutorial',
            ]);
    }
    public function the_kcb_thong_minh()
    {
        return $this->hasMany(Card::class,  'patient_id', 'tdl_patient_id');
    }
    public function don_xuat()
    {
        return $this->hasMany(ExpMest::class,  'service_req_id', 'id')
        ->leftJoin('his_medi_stock', 'his_medi_stock.id', '=', 'his_exp_mest.medi_stock_id')
        ->leftJoin('his_exp_mest aggr_exp_mest', 'aggr_exp_mest.id', '=', 'his_exp_mest.aggr_exp_mest_id')
        ->where('his_exp_mest.is_delete', 0)
        ->select([
            'his_exp_mest.id',
            'his_exp_mest.service_req_id',
            'his_medi_stock.medi_stock_code',
            'his_medi_stock.medi_stock_name',
            'his_exp_mest.exp_mest_code',
            'aggr_exp_mest.exp_mest_code as aggr_exp_mest_code', // mã phiếu lĩnh
            'his_exp_mest.IS_HOME_PRES', // đơn mang về
            'his_exp_mest.IS_KIDNEY', // đơn chạy thận
        ]);
    }
}
