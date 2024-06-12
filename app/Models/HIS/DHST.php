<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dhst extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_DHST';
    protected $fillable = [

    ];
    public function antibiotic_requests()
    {
        return $this->hasMany(AntibioticRequest::class);
    }
    public function cares()
    {
        return $this->hasMany(Care::class);
    }
    public function ksk_generals()
    {
        return $this->hasMany(KskGeneral::class);
    }
    public function ksk_occupationals()
    {
        return $this->hasMany(KskOccupational::class);
    }
    public function service_reqs()
    {
        return $this->hasMany(ServiceReq::class);
    }
}
