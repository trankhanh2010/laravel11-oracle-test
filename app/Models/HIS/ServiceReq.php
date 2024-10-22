<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceReq extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_service_req';
    protected $fillable = [
    ];

    public function treatment()
    {
        return $this->belongsTo(Treatment::class, 'treatment_id');
    }
    public function bed_logs()
    {
        return $this->hasMany(BedLog::class);
    }
    public function drug_interventions()
    {
        return $this->hasMany(DrugIntervention::class);
    }
    public function exam_sere_dires()
    {
        return $this->hasMany(ExamSereDire::class);
    }
    public function exp_mests()
    {
        return $this->hasMany(ExpMest::class);
    }
    public function ksk_drivers()
    {
        return $this->hasMany(KskDriver::class);
    }
    public function his_ksk_driver_cars()
    {
        return $this->hasMany(KskDriverCar::class, 'tdl_treatment_id');
    }
    public function ksk_generals()
    {
        return $this->hasMany(KskGeneral::class);
    }
    public function ksk_occupationals()
    {
        return $this->hasMany(KskOccupational::class);
    }
    public function ksk_others()
    {
        return $this->hasMany(KskOther::class);
    }
    public function ksk_over_eighteens()
    {
        return $this->hasMany(KskOverEighteen::class);
    }
    public function ksk_period_drivers()
    {
        return $this->hasMany(KskPeriodDriver::class);
    }
    public function ksk_under_eighteens()
    {
        return $this->hasMany(KskUnderEighteen::class);
    }
    public function sere_servs()
    {
        return $this->hasMany(SereServ::class);
    }
    public function sere_serv_exts()
    {
        return $this->hasMany(SereServExt::class, 'tdl_service_req_id');
    }
    public function sere_serv_rations()
    {
        return $this->hasMany(SereServRation::class);
    }
    public function service_req_matys()
    {
        return $this->hasMany(ServiceReqMaty::class);
    }
    public function service_req_metys()
    {
        return $this->hasMany(ServiceReqMety::class);
    }
}
