<?php 
namespace App\Repositories;

use App\Models\HIS\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class ConfigRepository
{
    protected $config;
    public function __construct(Config $config)
    {
        $this->config = $config;
    }
    public function getHisTranReqExpiredTimeOption()
    {
        $cacheKey = 'his_tran_req_expired_time_option';
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 10080, function () {
            return (int) $this->config->where('key', 'MOS.HIS_TRAN_REQ.EXPIRED_TIME.OPTION')->first()->value;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
        return $data;
    }
}