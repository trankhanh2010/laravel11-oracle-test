<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpMestMedicine extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_EXP_MEST_MEDICINE';
    protected $fillable = [

    ];
    public function bcs_mety_req_dts()
    {
        return $this->hasMany(BcsMetyReqDt::class);
    }
    public function imp_mest_medi_reqs()
    {
        return $this->hasMany(ImpMestMediReq::class, 'Th_exp_mest_medicine_id');
    }
    public function medicine_beans()
    {
        return $this->hasMany(MedicineBean::class);
    }
}
