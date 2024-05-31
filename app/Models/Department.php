<?php

namespace App\Models;

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
        'allow_treatment_type_ids',
        'default_instr_patient_type_id',
        'req_surg_treatment_type_id',
    ];
    public function room()
    {
        return $this->hasOne(Room::class);
    }

    public function allow_treatment_types()
    {
        return TreatmentType::whereIn('id', explode(',', $this->allow_treatment_type_ids))->get();
    }

    public function req_surg_treatment_type()
    {
        return $this->belongsTo(TreatmentType::class, 'req_surg_treatment_type_id');
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
