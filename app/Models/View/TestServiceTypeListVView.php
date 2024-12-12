<?php

namespace App\Models\View;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestServiceTypeListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'v_his_test_service_type_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
}
