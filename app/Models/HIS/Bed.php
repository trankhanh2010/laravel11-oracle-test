<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Bed extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Bed';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function bed_room()
    {
        return $this->belongsTo(BedRoom::class, 'bed_room_id');
    }

    public function bed_type()
    {
        return $this->belongsTo(BedType::class, 'bed_type_id');
    }

    public function treatment_room()
    {
        return $this->belongsTo(TreatmentRoom::class, 'treatment_room_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, BedBsty::class, 'bed_id', 'bed_service_type_id');
    }
    public static function get_data_from_db_to_elastic($id = null){
        $data = DB::connection('oracle_his')->table('his_bed')
        ->leftJoin('his_bed_type', 'his_bed.bed_type_id', '=', 'his_bed_type.id')
        ->leftJoin('his_bed_room', 'his_bed.bed_room_id', '=', 'his_bed_room.id')
        ->leftJoin('his_room', 'his_bed_room.room_id', '=', 'his_room.id')
        ->leftJoin('his_department', 'his_room.department_id', '=', 'his_department.id')
        ->select(
            'his_bed.*',
            'his_bed_type.bed_type_name',
            'his_bed_type.bed_type_code',
            'his_bed_room.bed_room_name',
            'his_bed_room.bed_room_code',
            'his_department.department_name',
            'his_department.department_code'
        );
        if($id != null){
            $data = $data->where('his_bed.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
