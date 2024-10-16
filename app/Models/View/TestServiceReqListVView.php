<?php

namespace App\Models\View;

use App\Models\HIS\SereServ;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestServiceReqListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'V_HIS_test_service_req_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function TestServiceTypeList()
    {
        return $this->hasMany(SereServ::class, 'service_req_id');
    }
}
