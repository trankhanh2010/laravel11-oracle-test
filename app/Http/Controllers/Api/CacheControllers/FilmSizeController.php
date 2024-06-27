<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\FilmSize;
use Illuminate\Http\Request;

class FilmSizeController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->film_size = new FilmSize();
    }
    public function film_size($id = null)
    {
        if ($id == null) {
            $name = $this->film_size_name;
            $param = [
            ];
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->film_size->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $name = $this->film_size_name . '_' . $id;
            $param = [
            ];
        }
        $data = get_cache_full($this->film_size, $param, $name, $id, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }

}
