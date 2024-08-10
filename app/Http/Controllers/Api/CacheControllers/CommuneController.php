<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use Illuminate\Http\Request;
use App\Models\SDA\Commune;
use App\Events\Cache\DeleteCache;
use App\Http\Requests\Commune\CreateCommuneRequest;
use App\Http\Requests\Commune\UpdateCommuneRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CommuneController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->commune = new Commune();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->commune);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function commune($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }
        try {
        $keyword = $this->keyword;
        if($keyword != null){
            $data = $this->commune
            ->leftJoin('sda_district as district', 'district.id', '=', 'sda_commune.district_id')
            ->select(
                'sda_commune.*',
                'district.district_name as district_name',
                'district.district_code as district_code',
            );
            $data = $data->where(function ($query) use ($keyword){
                $query = $query
                    ->where(DB::connection('oracle_his')->raw('sda_commune.commune_code'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('sda_commune.commune_name'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('sda_commune.search_code'), 'like', $keyword . '%');
            });
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('sda_commune.is_active'), $this->is_active);
            });
        } 
                    $count = $data->count();
                    if ($this->order_by != null) {
                        foreach ($this->order_by as $key => $item) {
                            $data->orderBy('sda_commune.'.$key, $item);
                        }
                    }
                    if($this->get_all){
                        $data = $data
                        ->get();
                    }else{
                        $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                    }
        }else{
            if ($id == null) {
                $data = Cache::remember($this->commune_name. '_start_' . $this->start . '_limit_' . $this->limit. $this->order_by_tring. '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function (){
                    $data = $this->commune
                    ->leftJoin('sda_district as district', 'district.id', '=', 'sda_commune.district_id')
                    ->select(
                        'sda_commune.*',
                        'district.district_name as district_name',
                        'district.district_code as district_code',
                    );
                    if ($this->is_active !== null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw('sda_commune.is_active'), $this->is_active);
                        });
                    } 
                    $count = $data->count();
                    if ($this->order_by != null) {
                        foreach ($this->order_by as $key => $item) {
                            $data->orderBy('sda_commune.'.$key, $item);
                        }
                    }
                    if($this->get_all){
                        $data = $data
                        ->get();
                    }else{
                        $data = $data
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                    }
                    return ['data' => $data, 'count' => $count];
                });
            } else {
                if (!is_numeric($id)) {
                    return return_id_error($id);
                }
                $check_id = $this->check_id($id, $this->commune, $this->commune_name);
                if($check_id){
                    return $check_id; 
                }
                $data = Cache::remember($this->commune_name.'_'.$id. '_is_active_' . $this->is_active, $this->time, function () use ($id){
                    $data = DB::connection('oracle_sda')->table('sda_commune as commune')
                    ->leftJoin('sda_district as district', 'district.id', '=', 'commune.district_id')
                    ->select(
                        'commune.*',
                        'district.district_name as district_name',
                        'district.district_code as district_code',
                    )
                    ->where('commune.id', $id);
                    if ($this->is_active !== null) {
                        $data = $data->where(function ($query) {
                            $query = $query->where(DB::connection('oracle_his')->raw("commune.is_active"), $this->is_active);
                        });
                    } 
                    $data = $data->first();
                    return $data;
                });
            }  
        }
        $param_return = [
            $this->get_all_name => $this->get_all,
            $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
            $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
            $this->count_name => $count ?? (is_array($data) ? $data['count'] : null  ),
            $this->is_active_name => $this->is_active,
            $this->keyword_name => $this->keyword,
            $this->order_by_name => $this->order_by_request
        ];
        return return_data_success($param_return, $data?? ($data['data'] ?? null));
    } catch (\Exception $e) {
        // Xử lý lỗi và trả về phản hồi lỗi
        return return_500_error();
    }
    }
    public function commune_create(CreateCommuneRequest $request)
    {
        $data = $this->commune::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_creator' => $this->app_creator,
            'app_modifier' => $this->app_modifier,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'district_id' => $request->district_id,
        ]);
        // Gọi event để xóa cache
        event(new DeleteCache($this->commune_name));
        return return_data_create_success($data);
    }

    public function commune_update(UpdateCommuneRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->commune->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data_update = [
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
            'app_modifier' => $this->app_modifier,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'search_code' => $request->search_code,
            'initial_name' => $request->initial_name,
            'district_id' => $request->district_id,
            'is_active' => $request->is_active,

        ];
        $data->fill($data_update);
        $data->save();
        // Gọi event để xóa cache
        event(new DeleteCache($this->commune_name));
        return return_data_update_success($data);
    }

    public function commune_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->commune->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->commune_name));
            return return_data_delete_success();
        } catch (\Exception $e) {
            return return_data_delete_fail();
        }
    }
}
