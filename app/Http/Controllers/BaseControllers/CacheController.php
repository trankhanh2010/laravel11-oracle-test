<?php

namespace App\Http\Controllers\BaseControllers;

use App\Events\Cache\DeleteCache;
use Illuminate\Http\Request;

class CacheController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
    
    }
    public function clear_cache(Request $request){
        $table_name = $request->table.'_name' ?? 'a';

        if(!isset($this->$table_name)){
            return return_param_error();
        }
        try{
            event(new DeleteCache($this->$table_name));
            return return_clear_cache();
        } catch (\Exception $e) {
            return return_500_error();
        }
    }
}
