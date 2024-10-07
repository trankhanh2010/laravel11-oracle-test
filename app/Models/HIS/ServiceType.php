<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Service_Type';    
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function service()
    {
        return $this->hasMany(Service::class);
    }

    public function exe_service_module()
    {
        return $this->belongsTo(ExeServiceModule::class, 'exe_service_module_id', 'id');
    }
}
