<?php

namespace App\Models\SDA;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_sda'; 
    protected $table = 'sda_province';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function national()
    {
        return $this->belongsTo(National::class, 'national_id');
    }
    public function districts()
    {
        return $this->hasMany(District::class);
    }
}
