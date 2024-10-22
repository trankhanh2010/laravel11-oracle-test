<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_treatment_type';
    protected $guarded = [
        'id',
    ];
    public $timestamps = false;
    public function required_service()
    {
        return $this->belongsTo(Service::class, 'required_service_id', 'id');
    }
}
