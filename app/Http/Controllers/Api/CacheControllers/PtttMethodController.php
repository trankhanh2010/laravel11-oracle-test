<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\PtttMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
class PtttMethodController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->pttt_method = new PtttMethod();
    }
    public function pttt_method($id = null)
    {
        if ($id == null) {
            $data = Cache::remember($this->pttt_method_name, $this->time, function (){
                return DB::connection('oracle_his')->table('his_pttt_method as pttt_method')
                ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'pttt_method.pttt_group_id')
                ->select(
                    'pttt_method.*',
                    'pttt_group.pttt_group_name',
                    'pttt_group.pttt_group_code',
                )
                ->get();
            });
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->pttt_method->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->pttt_method_name . '_' . $id;
            $param = [
                'pttt_group'
            ];
            $data = get_cache_full($this->pttt_method, $param, $name, $id, $this->time);

        }
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
