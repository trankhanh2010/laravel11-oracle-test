<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Refectory extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_refectory';
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
        $department = DB::connection('oracle_his')->table('his_refectory')
            ->join('his_room', 'his_refectory.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_refectory.id', $id)
            ->first();
        return $department;
    }
}
