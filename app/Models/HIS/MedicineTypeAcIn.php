<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicineTypeAcIn extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Medicine_Type_AcIn';
    protected $fillable = [

    ];
    public function medicine_type()
    {
        return $this->belongsTo(MedicineType::class, 'medicine_type_id');
    }

    public function active_ingredient()
    {
        return $this->belongsTo(ActiveIngredient::class, 'active_ingredient_id');
    }
}
