<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DataStore extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Data_Store';
    protected $guarded = [
        'id',
    ];
    public $timestamps = false;
    // protected $appends = [
    //     'treatment_types',
    //     'treatment_end_types',
    // ];
    // public function getTreatmentTypesAttribute()
    // {
    //     $treatment_type_ids = $this->treatment_type_ids;
    //     if($treatment_type_ids != null){
    //         return Cache::remember('treatment_type_ids_' . $treatment_type_ids, $this->time, function () use ( $treatment_type_ids) {
    //             return TreatmentType::select('id', 'treatment_type_code', 'treatment_type_name')->whereIn('id', explode(',', $treatment_type_ids))->get();
    //         });
    //     }
    //     return null;
       
    // }

    // public function getTreatmentEndTypesAttribute()
    // {
    //     if($this->treatment_end_type_ids != null){
    //         return Cache::remember('treatment_end_type_ids_' . $this->treatment_end_type_ids, $this->time, function ()  {
    //         $data = TreatmentEndType::select('id', 'treatment_end_type_code', 'treatment_end_type_name')->whereIn('id', explode(',', $this->treatment_end_type_ids))->get();
    //         return $data;
    //     });

    //     }
    //     return null;

    // }
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function stored_room()
    {
        return $this->belongsTo(Room::class, 'store_room_id');
    }

    public function parent()
    {
        return $this->belongsTo(DataStore::class, 'parent_id');
    }

    public function treatment_types()
    {
        return TreatmentType::whereIn('id', explode(',', $this->treatment_type_ids))->get();
    }

    public function treatment_end_types()
    {
        return TreatmentEndType::whereIn('id', explode(',', $this->treatment_type_end_ids))->get();
    }

    public function department_room($id)
    {
        $department = DB::connection('oracle_his')->table('his_data_store')
            ->join('his_room', 'his_data_store.room_id', '=', 'his_room.id')
            ->join('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select('his_department.*')
            ->where('his_data_store.id', $id)
            ->first();
        return $department;
    }
    public function stored_department()
    {
        return $this->belongsTo(Department::class, 'stored_department_id');
    }
}
