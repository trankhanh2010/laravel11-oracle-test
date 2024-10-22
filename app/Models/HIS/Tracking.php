<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_tracking';
    protected $fillable = [

    ];
    public function cares()
    {
        return $this->hasMany(Care::class, 'tracking_id');
    }
    public function debates()
    {
        return $this->hasMany(Debate::class, 'tracking_id');
    }
    public function dhsts()
    {
        return $this->hasMany(DHST::class, 'tracking_id');
    }
    public function service_reqs()
    {
        return $this->hasMany(ServiceReq::class, 'tracking_id');
    }
    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }


}
