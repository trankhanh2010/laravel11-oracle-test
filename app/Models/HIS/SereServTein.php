<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServTein extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_sere_serv_tein';
    protected $fillable = [

    ];
    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
    public function test_index()
    {
        return $this->belongsTo(TestIndex::class);
    }
}
