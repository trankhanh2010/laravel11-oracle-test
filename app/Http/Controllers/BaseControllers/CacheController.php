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
        if($request->table === null){
            Redis::select(config('database')['redis']['cache']['database']);  // Chuyển về db cache
            Redis::flushDB();
        }
        if($request->table != null){
            $tableName = Str::camel($request->table . 'Name' ?? 'a');
            if (!isset($this->$tableName)) {
                return returnParamError();
            }
            event(new DeleteCache($this->$tableName));
        }
        return returnClearCache();
    }
    public function clearCacheElaticIndexKeyword(Request $request)
    {
        event(new DeleteCache('elastic_index_keyword_' . $request->index));
        return returnClearCache();
    }
}
