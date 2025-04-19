<?php

namespace App\Models\View;

use App\Models\HIS\SereServExt;
use App\Models\HIS\SereServTein;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResultClsVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_result_cls';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
