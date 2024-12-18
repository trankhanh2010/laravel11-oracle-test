<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediStockMety extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_medi_stock_mety';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function medi_stock()
    {
        return $this->belongsTo(MediStock::class, 'medi_stock_id');
    }

    public function medicine_type()
    {
        return $this->belongsTo(MedicineType::class, 'medicine_type_id');
    }

    public function exp_medi_stock()
    {
        return $this->belongsTo(MediStock::class, 'exp_medi_stock_id');
    }

}
