<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\RationGroup;
use Illuminate\Http\Request;

class RationGroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->ration_group = new RationGroup();
    }
    public function ration_group($id = null)
    {
        if ($id == null) {
            $name = $this->ration_group_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->ration_group->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->ration_group_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->ration_group, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }

}
