<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commune extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_sda'; 
    protected $table = 'SDA_Commune';
    protected $fillable = [
        'district_id'
    ];
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
