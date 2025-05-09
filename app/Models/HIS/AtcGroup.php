<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AtcGroup extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_atc_group';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_atc_group')
        ->select(
            'his_atc_group.*'
        );
        if($id != null){
            $data = $data->where('his_atc_group.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}
