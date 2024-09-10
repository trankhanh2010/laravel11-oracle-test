<?php

namespace App\Http\Controllers\BaseControllers;

use App\Events\Cache\DeleteCache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CacheController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
    
    }
    public function clearCache(Request $request){
        $tableName = Str::camel($request->table.'Name' ?? 'a');
        if(!isset($this->$tableName)){
            return returnParamError();
        }
        try{
            event(new DeleteCache($this->$tableName));
            return returnClearCache();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return500Error($e->getMessage());
        }
    }
    public function clearCacheElaticIndexKeyword(Request $request){
        try{
            event(new DeleteCache('elastic_index_keyword_'.$request->index));
            return returnClearCache();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return500Error($e->getMessage());
        }
    }
}
