<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTypeAlter extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_patient_type_alter';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function patient_type(){
        return $this->belongsTo(PatientType::class);
    }
    public function treatment_type(){
        return $this->belongsTo(TreatmentType::class);
    }
}
