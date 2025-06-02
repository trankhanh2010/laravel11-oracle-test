<?php

namespace App\Models\View;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class YeuCauKhamClsPtttVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_yeu_cau_kham_cls_pttt';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
