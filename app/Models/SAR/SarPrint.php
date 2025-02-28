<?php

namespace App\Models\SAR;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SarPrint extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_sar'; 
    protected $table = 'sar_print';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}