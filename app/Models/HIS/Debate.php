<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Debate extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'HIS_Debate';
    protected $fillable = [

    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }
    public function icddelete()
    {
        return $this->belongsTo(Icd::class, 'icd_id_delete');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
    public function debate_type()
    {
        return $this->belongsTo(DebateType::class, 'debate_type_id');
    }
    public function surgery_service()
    {
        return $this->belongsTo(Service::class, 'surgery_service_id');
    }
    public function emotionless_method()
    {
        return $this->belongsTo(EmotionlessMethod::class, 'emotionless_method_id');
    }
    public function pttt_method()
    {
        return $this->belongsTo(PtttMethod::class, 'pttt_method_id');
    }
    public function tracking()
    {
        return $this->belongsTo(Tracking::class, 'tracking_id');
    }
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
    public function debate_reason()
    {
        return $this->belongsTo(DebateReason::class, 'debate_reason_id');
    }
    public function medicine_types()
    {
        return MedicineType::whereIn('id', explode(',', $this->medicine_type_ids))->get();
    }
    public function active_ingredients()
    {
        return ActiveIngredient::whereIn('id', explode(',', $this->active_ingredient_ids))->get();
    }
}
