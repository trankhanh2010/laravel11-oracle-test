<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Area extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Area';
    protected $fillable = [
        'create_time',
        'modify_time',
        'creator',
        'modifier',
        'app_creator',
        'app_modifier',
        'is_active',
        'is_delete',
        'area_code',
        'area_name',
        'department_id',
        'patient_type_id',

    ];
    // Đặt thuộc tính $timestamps thành false để tắt tự động thêm created_at và updated_at
    public $timestamps = false;
    public function department()
    {
        return $this->hasOne(Department::class);
    }
    public static function get_data_from_db_to_elastic($id = null){
        $data = DB::connection('oracle_his')->table('his_area')
        ->select(
            'his_area.*'
        );
        if($id != null){
            $data = $data->where('his_area.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
