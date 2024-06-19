<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExecuteRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_EXECUTE_ROOM';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function room()
    {
        return $this->belongsTo(Room::class);
    }   

    public function services()
    {
        return $this->belongsToMany(Service::class, ServiceRoom::class, 'room_id', 'service_id');
    }
    public function department($id)
    {
        $department = DB::connection('oracle_his')->table('his_execute_room')
            ->join('his_room', 'his_execute_room.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_execute_room.id', $id)
            ->first();
        return $department;
    }

    public function medi_stocks()
    {
        return $this->belongsToMany(MediStock::class, MestRoom::class, 'medi_stock_id', 'room_id');
    }
    public function rooms()
    {
        return $this->belongsToMany(Room::class, ExroRoom::class, 'execute_room_id', 'room_id')
        ->withPivot('is_hold_order', 'is_allow_request', 'is_priority_require');
    }
}
