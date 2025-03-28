<?php

namespace App\Listeners\Cache;

use App\Events\Cache\DeleteCache as CacheDeleteCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
class DeleteCache
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(CacheDeleteCache $event)
    {
        // Lấy danh sách key từ Redis Set thay vì dùng KEYS
        $cacheKeySet = "cache_keys:" . $event->modelName;
        $keys = Redis::connection('cache')->smembers($cacheKeySet);
    
        foreach ($keys as $key) {
            Redis::connection('cache')->del($key);
        }
    
        // Xóa luôn danh sách key trong Set
        Redis::connection('cache')->del($cacheKeySet);
    }
    
}
