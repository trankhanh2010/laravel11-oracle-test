<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServPttt extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Sere_Serv_Pttt';
    protected $fillable = [

    ];
    public function serv_segr()
    {
        return $this->belongsTo(ServSegr::class, 'sere_serv_id');
    }

    public function pttt_group()
    {
        return $this->belongsTo(ServSegr::class, 'pttt_group_id');
    }
}
