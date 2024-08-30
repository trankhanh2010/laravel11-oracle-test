<?php 
namespace App\Repositories;

use App\Models\HIS\AccidentLocation;
use Illuminate\Support\Facades\DB;

class AccidentLocationRepository
{
    protected $accident_location;

    public function __construct(AccidentLocation $accident_location)
    {
        $this->accident_location = $accident_location;
    }

    public function applyJoins()
    {
        return $this->accident_location
            ->select(
                'his_accident_location.*'
            );
    }

    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_location.accident_location_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_accident_location.accident_location_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $is_active)
    {
        if ($is_active !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_location.is_active'), $is_active);
        }

        return $query;
    }
    public function applyOrdering($query, $order_by, $order_by_join)
    {
        if ($order_by != null) {
            foreach ($order_by as $key => $item) {
                if (in_array($key, $order_by_join)) {

                } else {
                    $query->orderBy('his_accident_location.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $get_all, $start, $limit)
    {
        if ($get_all) {
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
        return $this->accident_location->find($id);
    }
    public function create($request, $time, $app_creator, $app_modifier){
        $data = $this->accident_location::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $app_creator,
            'app_modifier' => $app_modifier,
            'is_active' => 1,
            'is_delete' => 0,
            'accident_location_code' => $request->accident_location_code,
            'accident_location_name' => $request->accident_location_name,
        ]);
        return $data;
    }

    public function update($request, $data, $time, $app_modifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $app_modifier,
            'accident_location_code' => $request->accident_location_code,
            'accident_location_name' => $request->accident_location_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }

    public function delete($data){
        $data->delete();
        return $data;
    }
}
