<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HIS\RoomGroup;
use App\Http\Requests\RoomGroup\CreateRoomGroupRequest;
use App\Events\Cache\DeleteCache;

class RoomGroupController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->room_group = new RoomGroup();

    }
    public function room_group()
    {
        $name = $this->room_group_name;
        $param = [];
        $data = get_cache_full($this->room_group, $param, $name, null, $this->time);
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
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
