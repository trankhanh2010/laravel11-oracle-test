<?php

namespace App\Models\EMR;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sign extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_emr'; 
    protected $table = 'emr_sign';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}

