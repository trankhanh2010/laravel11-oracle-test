<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\SuimIndex;
use Illuminate\Http\Request;

class SuimIndexController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->suim_index = new SuimIndex();
    }
    public function suim_index($id = null)
    {
        if ($id == null) {
            $name = $this->suim_index_name;
            $param = [];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->suim_index->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->suim_index_name . '_' . $id;
            $param = [];
        }
        $data = get_cache_full($this->suim_index, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }

}
