<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TreatmentEndTypeExt extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_treatment_end_type_ext';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
