<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomGroup extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Room_Group';
    public $timestamps = false;
    protected $fillable = [
        'create_time',
        'modify_time',
        'creator',
        'modifier',
        'app_creator',
        'app_modifier',
        'is_active',
        'is_delete',
        'group_code',
        'room_group_code',
        'room_group_name'
    ];
}
