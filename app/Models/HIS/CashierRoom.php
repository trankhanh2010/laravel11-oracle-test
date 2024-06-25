<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class CashierRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Cashier_Room';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function department($id)
    {
        $department = DB::connection('oracle_his')->table('his_cashier_room')
            ->join('his_room', 'his_cashier_room.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_cashier_room.id', $id)
            ->first();
        return $department;
    }

    public function room_type($id)
    {
        $room_type = DB::connection('oracle_his')->table('his_cashier_room')
            ->join('his_room', 'his_cashier_room.room_id', '=', 'his_room.id')
            ->join('his_room_type', 'his_room.room_type_id', '=', 'his_room_type.id')
            ->select('his_room_type.*')
            ->where('his_cashier_room.id', $id)
            ->first();
        return $room_type;
    }
}
