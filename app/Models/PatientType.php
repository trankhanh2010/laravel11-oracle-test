<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_patient_type ';

    public function reception_rooms()
    {
        return $this->hasMany(ReceptionRoom::class, 'patient_type_ids', 'id');
    }
}
