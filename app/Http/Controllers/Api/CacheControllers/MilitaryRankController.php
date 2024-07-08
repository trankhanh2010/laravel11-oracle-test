<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\MilitaryRank;
use Illuminate\Support\Facades\DB;

class MilitaryRankController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->military_rank = new MilitaryRank();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!$this->military_rank->getConnection()->getSchemaBuilder()->hasColumn($this->military_rank->getTable(), $key)) {
                    unset($this->order_by_request[camelCaseFromUnderscore($key)]);       
                    unset($this->order_by[$key]);               
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function military_rank($id = null)
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword !== null) {
            $param = [
            ];
            $data = $this->military_rank;
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                ->where(DB::connection('oracle_his')->raw('lower(military_rank_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(military_rank_name)'), 'like', '%' . $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('is_active'), $this->is_active);
            });
        } 
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
                $name = $this->military_rank_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring;
                $param = [];
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $data = $this->military_rank->find($id);
                if ($data == null) {
                    return return_not_record($id);
                }
                $name = $this->military_rank_name . '_' . $id;
                $param = [];
            }
            $data = get_cache_full($this->military_rank, $param, $name, $id, $this->time, $this->start, $this->limit, $this->order_by);
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
