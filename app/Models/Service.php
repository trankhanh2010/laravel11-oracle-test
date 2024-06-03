<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Service';
 
    protected $fillable = [
        'service_type_id',
        'parent_id',
        'service_unit_id',
        'hein_service_type_id',
        'bill_patient_type_id',
        'pttt_group_id',
        'pttt_method_id',
        'icd_cm_id',
        'revenue_department_id',
        'package_id',
        'exe_service_module_id',
        'gender_id',
        'ration_group_id',
        'diim_type_id',
        'fuex_type_id',
        'test_type_id',
        'other_pay_source_id',
        'body_part_ids',
        'film_size_id',
        'applied_patient_type_ids',
        'default_patient_type_id',
        'applied_patient_classify_ids',
        'min_proc_time_except_paty_ids',
        'max_proc_time_except_paty_ids',
        'total_time_except_paty_ids',
        'service_code',
    ];

    public function service_type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

}
