<?php

namespace App\Http\Controllers\BaseControllers;

use App\Events\Cache\DeleteCache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;

class CacheController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController

    }
    public function clearCache(Request $request)
    {
        // Nếu xóa hết cache
        if(in_array('all',$this->keys)){
            // Redis::select(config('database')['redis']['cache']['database']);  // Chuyển về db cache
            Redis::connection('cache')->flushAll();
        }

        // Nếu xóa theo param
        foreach($this->keys as $key => $item){
            event(new DeleteCache($item));
        }
        return returnClearCache();
    }
    public function clearCacheElaticIndexKeyword(Request $request)
    {
        event(new DeleteCache('elastic_index_keyword_' . $request->index));
        return returnClearCache();
    }
}
