<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Service_Room';
    protected $fillable = [
        'service_id',
        'room_id'
    ];

    public function service()
    {
        return $this->belongsTo(Servive::class, 'service_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    
    public function execute_room()
    {
        return $this->belongsTo(ExecuteRoom::class, 'room_id');
    }
}
