<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ExecuteRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_EXECUTE_ROOM';
    public function room()
    {
        return $this->belongsTo(Room::class);
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
}
