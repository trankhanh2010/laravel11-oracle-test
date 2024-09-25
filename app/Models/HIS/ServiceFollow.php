<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceFollow extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Service_Follow';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function follow()
    {
        return $this->belongsTo(Service::class, 'follow_id');
    }

    public function treatment_types()
    {
        return TreatmentType::whereIn('id', explode(',', $this->treatment_type_ids))->get();
    }
}
