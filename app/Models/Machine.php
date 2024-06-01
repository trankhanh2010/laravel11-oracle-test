<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Machine extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Machine';

    protected $fillable = [
        'room_id',
        'room_ids',
        'department_id'
    ];

    public function rooms()
    {
        return Room::whereIn('id', explode(',', $this->room_ids))->get();
    }
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

}
