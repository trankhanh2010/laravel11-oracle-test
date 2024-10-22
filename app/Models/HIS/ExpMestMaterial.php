<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpMestMaterial extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_exp_mest_material';
    protected $fillable = [

    ];
    public function bcs_maty_req_dts()
    {
        return $this->hasMany(BcsMatyReqDt::class);
    }
    public function imp_mest_mate_reqs()
    {
        return $this->hasMany(ImpMestMateReq::class, 'th_exp_mest_material_id');
    }
    public function material_beans()
    {
        return $this->hasMany(MaterialBean::class);
    }
}
