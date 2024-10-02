<?php

namespace App\Models\SDA;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_sda';
    protected $table = 'SDA_Group_Type';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
