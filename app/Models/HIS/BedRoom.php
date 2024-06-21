<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class BedRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    // public $time = 604800;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'HIS_BED_ROOM';
    // Đặt thuộc tính $timestamps thành false để tắt tự động thêm created_at và updated_at
    public $timestamps = false;
    protected $appends = [
        'treatment_types',
    ];
    protected $guarded = [
        'id',
    ];
    // public function getTreatmentTypeIdsAttribute($value)
    // {
    //     if($value != null){
    //          // Tạo Cache để tránh trùng lặp truy vấn
    //          return Cache::remember('allow_treatment_type_ids_'.$value, $this->time, function () use ($value) {
    //             return TreatmentType::
    //             select('id', 'treatment_type_code', 'treatment_type_name')
    //             ->whereIn('id', explode(',', $value))->get();
    //         });        
    //     }else{
    //         return $value;
    //     }
    // }
    public function getTreatmentTypesAttribute()
    {
        $data = TreatmentType::whereIn('id', explode(',', $this->treatment_type_ids))->get();
        return $data;
    }

    // protected $fillable = [
    //     'create_time' ,
    //     'modify_time' ,
    //     'creator' ,
    //     'modifier' ,
    //     'app_creator' ,
    //     'app_modifier',
    //     'is_active' ,
    //     'is_delete' ,
    //     'bed_room_code',
    //     'bed_room_name' ,
    //     'is_surgery',
    //     'treatment_type_ids',
    //     'room_id' ,
    // ];
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function treatment_types()
    {
        return TreatmentType::whereIn('id', explode(',', $this->treatment_type_ids))->get();
    }

    public function department($id)
    {
        $department = DB::connection('oracle_his')->table('his_bed_room')
            ->join('his_room', 'his_bed_room.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_bed_room.id', $id)
            ->first();
        return $department;
    }
}
