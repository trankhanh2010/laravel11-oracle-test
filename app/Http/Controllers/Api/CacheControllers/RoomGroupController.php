<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\HIS\RoomGroup;
use App\Http\Requests\RoomGroup\CreateRoomGroupRequest;
use App\Events\Cache\DeleteCache;
use Illuminate\Support\Facades\DB;

class RoomGroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->room_group = new RoomGroup();

    }
    public function room_group()
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if ($keyword != null) {
            $param = [
            ];
            $data = $this->room_group
                ->where(DB::connection('oracle_his')->raw('lower(room_group_code)'), 'like', '%' . $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('lower(room_group_name)'), 'like', '%' . $keyword . '%');
            $count = $data->count();
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->with($param)
                ->get();
        } else {
            $name = $this->room_group_name. '_start_' . $this->start . '_limit_' . $this->limit;
            $param = [];
            $data = get_cache_full($this->room_group, $param, $name, null, $this->time, $this->start, $this->limit);
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }

    public function room_group_create(CreateRoomGroupRequest $request)
    {
        $data = $this->room_group::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'group_code' => $request->group_code,
            'room_group_name' => $request->room_group_name,
            'room_group_code' => $request->room_group_code,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->room_group_name));
        return return_data_create_success($data);
    }
}
