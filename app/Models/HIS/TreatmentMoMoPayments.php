<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentMoMoPayments extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_treatment_momo_payments';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function treatment()
{
    return $this->belongsTo(Treatment::class, 'treatment_id');
}

}
