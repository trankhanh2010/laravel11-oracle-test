<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\MilitaryRank;

class MilitaryRankController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->military_rank = new MilitaryRank();
    }
    public function military_rank($id = null)
    {
        if ($id == null) {
            $name = $this->military_rank_name;
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
        $data = get_cache_full($this->military_rank, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
