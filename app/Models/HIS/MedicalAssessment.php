<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAssessment extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_medical_assessment';
    protected $fillable = [

    ];
}
