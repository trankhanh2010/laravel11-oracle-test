<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AccidentCare extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Accident_Care';

    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_accident_care')
        ->select(
            'his_accident_care.*'
        );
        if($id != null){
            $data = $data->where('his_accident_care.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
