<?php

namespace App\Models\HIS;

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
        'department_id',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, ServiceMachine::class, 'machine_id', 'service_id');
    }

    public function execute_rooms()
    {
        // Lấy theo room_id trong Execute_Room
        return ExecuteRoom::whereIn('room_id', explode(',', $this->room_ids))->get();
    }
    public function execute_room()
    {
        return $this->belongsTo(ExecuteRoom::class, 'room_id');
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

}
