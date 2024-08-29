<?php 
namespace App\Repositories;

use App\Models\HIS\AccidentHurtType;
use Illuminate\Support\Facades\DB;

class AccidentHurtTypeRepository
{
    protected $accident_hurt_type;

    public function __construct(AccidentHurtType $accident_hurt_type)
    {
        $this->accident_hurt_type = $accident_hurt_type;
    }

    public function applyJoins()
    {
        return $this->accident_hurt_type
            ->select(
                'his_accident_hurt_type.*'
            );
    }

    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_hurt_type.accident_hurt_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_accident_hurt_type.accident_hurt_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $is_active)
    {
        if ($is_active !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_accident_hurt_type.is_active'), $is_active);
        }

        return $query;
    }
    public function applyOrdering($query, $order_by, $order_by_join)
    {
        if ($order_by != null) {
            foreach ($order_by as $key => $item) {
                if (in_array($key, $order_by_join)) {

                } else {
                    $query->orderBy('his_accident_hurt_type.' . $key, $item);
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
        return $this->accident_hurt_type->find($id);
    }
    public function create($request, $time, $app_creator, $app_modifier){
        $data = $this->accident_hurt_type::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $app_creator,
            'app_modifier' => $app_modifier,
            'is_active' => 1,
            'is_delete' => 0,
            'accident_hurt_type_code' => $request->accident_hurt_type_code,
            'accident_hurt_type_name' => $request->accident_hurt_type_name,
            'accident_hurt_type_type_id' => $request->accident_hurt_type_type_id,
            'accident_hurt_type_room_id' => $request->accident_hurt_type_room_id,
            'max_capacity' => $request->max_capacity,
            'is_accident_hurt_type_stretcher' => $request->is_accident_hurt_type_stretcher,
        ]);
        return $data;
    }

    public function update($request, $data, $time, $app_modifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $app_modifier,
            'accident_hurt_type_code' => $request->accident_hurt_type_code,
            'accident_hurt_type_name' => $request->accident_hurt_type_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }

    public function delete($data){
        $data->delete();
        return $data;
    }
}
