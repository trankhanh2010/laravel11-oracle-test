<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Medicine_Type';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function medi_stocks()
    {
        return $this->belongsToMany(MediStock::class, MediStockMety::class, 'medicine_type_id', 'medi_stock_id')
        ->withPivot('exp_medi_stock_id');
    }

    public function service_unit()
    {
        return $this->belongsTo(ServiceUnit::class, 'tdl_service_unit_id');
    }

    public function active_ingredients()
    {
        return $this->belongsToMany(ActiveIngredient::class, MedicineTypeAcIn::class, 'medicine_type_id', 'active_ingredient_id');
    }
}
