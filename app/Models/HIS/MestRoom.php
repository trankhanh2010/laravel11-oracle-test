<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MestRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_mest_room';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function execute_room()
    {
        return $this->belongsTo(ExecuteRoom::class, 'room_id', 'room_id');
    }

    public function medi_stock()
    {
        return $this->belongsTo(MediStock::class, 'medi_stock_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
