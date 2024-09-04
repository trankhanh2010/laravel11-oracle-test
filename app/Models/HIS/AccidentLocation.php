<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccidentLocation extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Accident_Location';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_accident_location')
        ->select(
            'his_accident_location.*'
        );
        if($id != null){
            $data = $data->where('his_accident_location.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
