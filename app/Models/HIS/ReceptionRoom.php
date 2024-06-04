<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
class ReceptionRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Reception_Room';

    protected $fillable = [
        'patient_type_ids',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function patient_types()
    {
        return PatientType::whereIn('id', explode(',', $this->patient_type_ids))->get();
    }

    public function department($id)
    {
        $department = DB::connection('oracle_his')->table('his_reception_room')
            ->join('his_room', 'his_reception_room.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_reception_room.id', $id)
            ->first();
        return $department;
    }
}
