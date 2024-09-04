<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AgeType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Age_Type';
    protected $fillable = [
    ];
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_age_type')
        ->select(
            'his_age_type.*'
        );
        if($id != null){
            $data = $data->where('his_age_type.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
