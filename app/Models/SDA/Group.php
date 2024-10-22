<?php

namespace App\Models\SDA;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_sda';
    protected $table = 'sda_group';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
