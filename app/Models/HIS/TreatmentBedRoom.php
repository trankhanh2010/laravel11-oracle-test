<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentBedRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_treatment_bed_room';
    protected $fillable = [

    ];
    public function treatment(){
        return $this->belongsTo(Treatment::class);
    }
    public function bed_room(){
        return $this->belongsTo(BedRoom::class);
    }
}
