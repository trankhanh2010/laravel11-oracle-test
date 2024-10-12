<?php 
namespace App\Repositories;

use App\Models\View\UserRoomVView;
use Illuminate\Support\Facades\DB;

class UserRoomVViewRepository
{
    protected $userRoomVView;
    public function __construct(UserRoomVView $userRoomVView)
    {
        $this->userRoomVView = $userRoomVView;
    }

    public function applyJoins()
    {
        return $this->userRoomVView
            ->select(
                'v_his_user_room.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_user_room.loginname'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('v_his_user_room.room_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_user_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_user_room.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->userRoomVView->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->userRoomVView::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'user_room_v_view_code' => $request->user_room_v_view_code,
            'user_room_v_view_name' => $request->user_room_v_view_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'user_room_v_view_code' => $request->user_room_v_view_code,
            'user_room_v_view_name' => $request->user_room_v_view_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($callback, $batchSize = 5000, $id = null)
    {
        $query = $this->applyJoins();
        if ($id != null) {
            $data = $query ->where('v_his_user_room.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data ;
            }
        } else {
            $batchData = [];
            $count = 0;
            foreach ($query->cursor() as $item) {
                $attributes = $item->getAttributes();
                $batchData[] = $attributes;
                $count++;
                
                if ($count % $batchSize == 0) {
                    $callback($batchData);
                    $batchData = [];
                }
            }
            if (!empty($batchData)) {
                $callback($batchData);
            }
        }
    }
}