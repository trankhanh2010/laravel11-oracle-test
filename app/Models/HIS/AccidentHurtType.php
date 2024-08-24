<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccidentHurtType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Accident_Hurt_Type';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public static function get_data_from_db_to_elastic($id = null){
        $data = DB::connection('oracle_his')->table('his_accident_hurt_type')
        ->select(
            'his_accident_hurt_type.*'
        );
        if($id != null){
            $data = $data->where('his_accident_hurt_type.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
