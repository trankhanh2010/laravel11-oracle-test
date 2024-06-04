<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedBsty extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Bed_Bsty';
    protected $fillable = [
        'bed_id',
        'bed_service_type_id',
    ];

    public function bed()
    {
        return $this->belongsTo(Bed::class, 'bed_id');
    }

    public function bed_service_type()
    {
        return $this->belongsTo(Service::class, 'bed_service_type_id');
    }
}
