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
        if (in_array('all', $this->keys) && !$this->tab) {
            // Redis::select(config('database')['redis']['cache']['database']);  // Chuyển về db cache
            Redis::connection('cache')->flushAll();
        }
        // Nếu là voBenhAn
        if ($this->tab == 'voBenhAn') {
            $this->keys =  [
                "setting", //Cache hỗ trợ
                "department",
                "pttt_catastrophe",
                "pttt_condition",
                "pttt_method",
                "service_req_type",
                "service_req_stt",
                "treatment_end_type",
                "emr_cover_type",
                "emr_form",
                "icd",
                "death_cause",
                "death_within",
                "medical_case_cover_list_v_view",
                "treatment_result",
                "user_room_v_view",
                "sere_serv_cls_list_v_view",
                "sere_serv_tein_charts_v_view",
                "treatment_list_v_view",
                "employee",
                "signer",
                "speed_unit",
                "repay_reason",
                "fund",
                "service_paty",
            ];
        }
        // Nếu xóa theo param
        foreach ($this->keys as $key => $item) {
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
