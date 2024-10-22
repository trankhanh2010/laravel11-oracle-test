<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActiveIngredient extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his';
    protected $table = 'his_active_ingredient';
    protected $fillable = [

    ];

    public function medicine_types()
    {
        return $this->belongsToMany(MedicineType::class, MedicineTypeAcIn::class, 'active_ingredient_id', 'medicine_type_id');
    }
}
