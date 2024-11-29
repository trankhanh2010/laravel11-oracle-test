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

 // Lấy tất cả các khóa chứa từ khóa
        $keys = Redis::connection('cache')->keys('*'.$event->modelName.'*');
        // Xóa từng khóa
        Redis::connection('cache')->pipeline(function ($pipe) use ($keys) {
            foreach ($keys as $key) {
                $pipe->del($key);
            }
        });
    }
}
