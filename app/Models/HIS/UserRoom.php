<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'his_user_room';
    protected $fillable = [

    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
}
