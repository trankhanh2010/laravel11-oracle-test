<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    // protected $connection = 'oracle_data'; // Kết nối CSDL khác
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'HIS_DEPARTMENT';
    protected $fillable = [
        'create_time',
        'modify_time',
        'creator',
        'modifier',
        'app_creator',
        'app_modifier',
        'is_active',
        'is_delete',
        'department_code',
        'department_name',
        'g_code',
        'bhyt_code',
        'branch_id',
        'allow_treatment_type_ids',
        'default_instr_patient_type_id',
        'theory_patient_count',
        'reality_patient_count',
        'req_surg_treatment_type_id',
        'phone' ,
        'head_loginname' ,
        'head_username',
        'accepted_icd_codes',
        'is_exam' ,
        'is_clinical',
        'allow_assign_package_price' ,
        'auto_bed_assign_option',
        'is_emergency' ,
        'is_auto_receive_patient' ,
        'allow_assign_surgery_price' ,
        'is_in_dep_stock_moba' ,
        'warning_when_is_no_surg' ,
    ];
     // Đặt thuộc tính $timestamps thành false để tắt tự động thêm created_at và updated_at
    public $timestamps = false;
    public function room()
    {
        return $this->hasOne(Room::class);
    }

    public function allow_treatment_types()
    {
        return TreatmentType::
            select('id', 'treatment_type_code', 'treatment_type_name')
            ->whereIn('id', explode(',', $this->allow_treatment_type_ids))->get();
    }

    public function req_surg_treatment_type()
    {
        return $this->belongsTo(TreatmentType::class, 'req_surg_treatment_type_id');
    }

    public function default_instr_patient_type()
    {
        return $this->belongsTo(PatientType::class, 'default_instr_patient_type_id');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
