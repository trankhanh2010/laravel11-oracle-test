<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DataStore extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Data_Store';
    protected $fillable = [
        'treatment_type_ids',
        'treatment_end_type_ids',
        'parent_id',
        'stored_department_id',
        'stored_room_id'
    ];

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
