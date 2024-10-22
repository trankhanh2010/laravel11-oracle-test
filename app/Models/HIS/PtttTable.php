<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtttTable extends Model
{
    use HasFactory,  dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_pttt_table';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function execute_room()
    {
        return $this->belongsTo(ExecuteRoom::class, 'execute_room_id');
    }
}
