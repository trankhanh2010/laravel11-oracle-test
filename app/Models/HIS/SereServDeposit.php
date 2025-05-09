<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServDeposit extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_sere_serv_deposit';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function seseDepoRepays()
    {
        return $this->hasMany(SeseDepoRepay::class, 'sere_serv_deposit_id', 'id');
    }
}
