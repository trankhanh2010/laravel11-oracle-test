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
class CommuneController extends BaseApiCacheController
{
    public function __construct(Request $request){
        parent::__construct($request); // Gọi constructor của BaseController
        $this->commune = new Commune();
    }
    public function commune($id = null)
    {
        if ($id == null) {
            $data = Cache::remember($this->commune_name, $this->time, function (){
                return DB::connection('oracle_sda')->table('sda_commune as commune')
                ->leftJoin('sda_district as district', 'district.id', '=', 'commune.district_id')
                ->select(
                    'commune.*',
                    'district.district_name as district_name',
                    'district.district_code as district_code',
                )
                ->get();
            });
        } else {
            if (!is_numeric($id)) {
                return return_id_error($id);
            }
            $data = $this->commune->find($id);
            if ($data == null) {
                return return_not_record($id);
            }
            $data = Cache::remember($this->commune_name.'_'.$id, $this->time, function () use ($id){
                return DB::connection('oracle_sda')->table('sda_commune as commune')
                ->leftJoin('sda_district as district', 'district.id', '=', 'commune.district_id')
                ->select(
                    'commune.*',
                    'district.district_name as district_name',
                    'district.district_code as district_code',
                )
                ->where('commune.id', $id)
                ->get();
            });
        }  
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
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
