<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediStockMaty extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Medi_Stock_Maty';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    
    public function medi_stock()
    {
        return $this->belongsTo(MediStock::class, 'medi_stock_id');
    }

    public function material_type()
    {
        return $this->belongsTo(MaterialType::class, 'material_type_id');
    }

    public function exp_medi_stock()
    {
        return $this->belongsTo(MediStock::class, 'exp_medi_stock_id');
    }
}
