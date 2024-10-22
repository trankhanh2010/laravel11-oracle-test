<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Bed extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_bed';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function bedRoom()
    {
        return $this->belongsTo(BedRoom::class, 'bed_room_id');
    }

    public function bedType()
    {
        return $this->belongsTo(BedType::class, 'bed_type_id');
    }

    public function treatmentRoom()
    {
        return $this->belongsTo(TreatmentRoom::class, 'treatment_room_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, BedBsty::class, 'bed_id', 'bed_service_type_id');
    }
}
