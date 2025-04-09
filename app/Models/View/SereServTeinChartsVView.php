<?php

namespace App\Models\View;

use App\Models\HIS\SereServExt;
use App\Models\HIS\SereServTein;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServTeinChartsVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'xa_v_his_sere_serv_tein_charts';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function getServiceDescriptionAttribute($value)
    {
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : []; // fallback nếu decode lỗi
    }
}
