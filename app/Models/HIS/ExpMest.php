<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpMest extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Exp_Mest';
    protected $fillable = [

    ];
    public function exp_blty_services()
    {
        return $this->hasMany(ExpBltyService::class);
    }
    public function exp_mest_bloods()
    {
        return $this->hasMany(ExpMestBlood::class);
    }
    public function exp_mest_blty_reqs()
    {
        return $this->hasMany(ExpMestBltyReq::class);
    }
    public function exp_mest_materials()
    {
        return $this->hasMany(ExpMestMaterial::class);
    }
    public function exp_mest_maty_reqs()
    {
        return $this->hasMany(ExpMestMatyReq::class);
    }
    public function exp_mest_medicines()
    {
        return $this->hasMany(ExpMestMedicine::class);
    }
    public function exp_mest_mety_reqs()
    {
        return $this->hasMany(ExpMestMetyReq::class);
    }
    public function exp_mest_users()
    {
        return $this->hasMany(ExpMestUser::class);
    }
    public function sere_serv_teins()
    {
        return $this->hasMany(SereServTein::class);
    }
    public function transaction_exps()
    {
        return $this->hasMany(TransactionExp::class);
    }
    public function vitamin_as()
    {
        return $this->hasMany(VitaminA::class);
    }
}
