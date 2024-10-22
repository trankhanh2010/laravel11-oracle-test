<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImpMest extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_imp_mest';
    protected $fillable = [

    ];
    public function imp_mest_medicines()
    {
        return $this->hasMany(ImpMestMedicine::class);
    }
}


