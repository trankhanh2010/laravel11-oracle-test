<?php

namespace App\Models\View;

use App\Models\HIS\ExpMestMedicine;
use App\Models\HIS\SereServExt;
use App\Models\HIS\SereServFile;
use App\Models\HIS\SereServMaty;
use App\Models\HIS\SereServPttt;
use App\Models\HIS\SereServTein;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServDetailVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'v_his_sere_serv_detail';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function sere_serv_exts()
    {
        return $this->hasMany(SereServExt::class,'sere_serv_id');
    }
    public function sere_serv_files()
    {
        return $this->hasMany(SereServFile::class,'sere_serv_id');
    }
    public function sere_serv_matys()
    {
        return $this->hasMany(SereServMaty::class,'sere_serv_id');
    }
    public function sere_serv_pttts()
    {
        return $this->hasMany(SereServPttt::class,'sere_serv_id');
    }
    public function exp_mest_medicine()
    {
        return $this->belongsTo(ExpMestMedicine::class);
    }
    public function sere_serv_teins()
    {
        return $this->hasMany(SereServTein::class,'tdl_service_req_id','service_req_id', );
    }
}
