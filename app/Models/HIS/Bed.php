<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bed extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Bed';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function bed_room()
    {
        return $this->belongsTo(BedRoom::class, 'bed_room_id');
    }

    public function bed_type()
    {
        return $this->belongsTo(BedType::class, 'bed_type_id');
    }

    public function treatment_room()
    {
        return $this->belongsTo(TreatmentRoom::class, 'treatment_room_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, BedBsty::class, 'bed_id', 'bed_service_type_id');
    }
}
