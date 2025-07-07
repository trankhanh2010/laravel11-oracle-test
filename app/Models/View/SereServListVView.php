<?php

namespace App\Models\View;

use App\Models\HIS\SereServExt;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_sere_serv_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function sere_serv_exts()
    {
        return $this->hasMany(SereServExt::class,'sere_serv_id')
        ->where('his_sere_serv_ext.is_delete', 0);
    }
}
