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
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if($keyword != null){
            $data = DB::connection('oracle_his')->table('his_pttt_method as pttt_method')
                    ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'pttt_method.pttt_group_id')
                    ->select(
                        'pttt_method.*',
                        'pttt_group.pttt_group_name',
                        'pttt_group.pttt_group_code',
                    )
                    ->where(DB::connection('oracle_his')->raw('lower(pttt_method_code)'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('lower(pttt_method_name)'), 'like', '%' . $keyword . '%');
                $count = $data->count();
                $data = $data    
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
        }else{
            if ($id == null) {
                $data = Cache::remember($this->pttt_method_name. '_start_' . $this->start . '_limit_' . $this->limit, $this->time, function (){
                    $data = DB::connection('oracle_his')->table('his_pttt_method as pttt_method')
                    ->leftJoin('his_pttt_group as pttt_group', 'pttt_group.id', '=', 'pttt_method.pttt_group_id')
                    ->select(
                        'pttt_method.*',
                        'pttt_group.pttt_group_name',
                        'pttt_group.pttt_group_code',
                    );
                    $count = $data->count();
                    $data = $data    
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                return ['data' => $data, 'count' => $count];
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
                $data = get_cache_full($this->pttt_method, $param, $name, $id, $this->time, $this->start, $this->limit);
            }
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
