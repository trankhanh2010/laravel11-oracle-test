<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\FuexType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuexTypeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->fuex_type = new FuexType();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->fuex_type->getConnection()->getSchemaBuilder()->hasColumn($this->fuex_type->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function fuex_type($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->fuex_type
                ->where(DB::connection('oracle_his')->raw('lower(fuex_type_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(fuex_type_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy($key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            if ($id == null) {
                $name = $this->fuex_type_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring;
                $param = [
                ];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->fuex_type->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->fuex_type_name . '_' . $id;
                $param = [
                ];
            }
            $data = get_cache_full($this->fuex_type, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count'],
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
}
