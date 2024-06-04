<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MestPatientType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Mest_Patient_Type';
    protected $fillable = [
        'medi_stock_id',
        'patient_type_id',
    ];

    public function medi_stock()
    {
        return $this->belongsTo(MediStock::class, 'medi_stock_id');
    }

    public function patient_type()
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id');
    }
}
