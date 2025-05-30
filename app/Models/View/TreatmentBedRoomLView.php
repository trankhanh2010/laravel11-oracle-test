<?php

namespace App\Models\View;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentBedRoomLView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'l_his_treatment_bed_room';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
