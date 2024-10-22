<?php

namespace App\Models\SDA;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ethnic extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_sda';
    protected $table = 'sda_ethnic';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
