<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Material_Type';

    public function medi_stocks()
    {
        return $this->belongsToMany(MediStock::class, MediStockMaty::class, 'material_type_id', 'medi_stock_id');
    }

    public function service_unit()
    {
        return $this->belongsTo(ServiceUnit::class, 'tdl_service_unit_id');
    }
}
