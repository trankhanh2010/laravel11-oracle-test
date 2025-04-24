<?php

namespace App\Models\EMR_FINAL;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Phieutdvacsbnc2Phieumau extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_emr_final'; 
    protected $table = 'phieutdvacsbnc2_phieumau';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}

