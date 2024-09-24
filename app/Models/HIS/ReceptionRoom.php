<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class ReceptionRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Reception_Room';

    public $timestamps = false;
    protected $appends = [
        'patient_types',
    ];
    protected $guarded = [
        'id',
    ];
    public function getPatientTypesAttribute()
    {
        $patient_type_ids = $this->patient_type_ids;
        if( $patient_type_ids != ""){
            return Cache::remember('patient_type_ids_' . $patient_type_ids, $this->time, function () use ( $patient_type_ids) {
                $data = PatientType::select(['patient_type_name', 'patient_type_code'])->whereIn('id', explode(',', $this->patient_type_ids))->get();
                return $data;
                        });
        }
        return null;
    }

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
