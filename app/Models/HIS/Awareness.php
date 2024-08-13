<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Awareness extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Awareness';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
