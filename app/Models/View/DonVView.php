<?php

namespace App\Models\View;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DonVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_don';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function beans()
    {
        // Lấy ra danh bean(medicine/material) khi sửa đơn
        return $this->hasMany(ThuocVatTuBeanVView::class, 'exp_mest_m_id', 'id')
        ->select([
            'bean_id',
            'exp_mest_m_id',
        ])
        ; 
    }
}
