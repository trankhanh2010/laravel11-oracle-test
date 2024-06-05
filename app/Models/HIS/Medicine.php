<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Medicine';
    protected $fillable = [
    ];

    public function medicine_type()
    {
        return $this->belongsTo(MedicineType::class, 'medicine_type_id');
    }
}
