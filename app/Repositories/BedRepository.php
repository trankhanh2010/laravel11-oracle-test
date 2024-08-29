<?php 
namespace App\Repositories;

use App\Models\HIS\Bed;
use Illuminate\Support\Facades\DB;

class BedRepository
{
    protected $bed;

    public function __construct(Bed $bed)
    {
        $this->bed = $bed;
    }

    public function applyJoins()
    {
        return $this->bed
            ->leftJoin('his_bed_type', 'his_bed.bed_type_id', '=', 'his_bed_type.id')
            ->leftJoin('his_bed_room', 'his_bed.bed_room_id', '=', 'his_bed_room.id')
            ->leftJoin('his_room', 'his_bed_room.room_id', '=', 'his_room.id')
            ->leftJoin('his_department', 'his_room.department_id', '=', 'his_department.id')
            ->select(
                'his_bed.*',
                'his_bed_type.bed_type_name',
                'his_bed_type.bed_type_code',
                'his_bed_room.bed_room_name',
                'his_bed_room.bed_room_code',
                'his_department.department_name',
                'his_department.department_code'
            );
    }

    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_bed.bed_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_bed.bed_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $is_active)
    {
        if ($is_active !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_bed.is_active'), $is_active);
        }

        return $query;
    }
    public function applyOrdering($query, $order_by, $order_by_join)
    {
        if ($order_by != null) {
            foreach ($order_by as $key => $item) {
                if (in_array($key, $order_by_join)) {
                    if (in_array($key, ['bed_type_name', 'bed_type_code'])) {
                        $query->orderBy('his_bed_type.' . $key, $item);
                    }
                    if (in_array($key, ['bed_room_name', 'bed_room_code'])) {
                        $query->orderBy('his_bed_room.' . $key, $item);
                    }
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('his_department.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_bed.' . $key, $item);
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
    public function getBedById($id)
    {
        return $this->bed->find($id);
    }
    public function createBed($request, $time, $app_creator, $app_modifier){
        $data = $this->bed::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $app_creator,
            'app_modifier' => $app_modifier,
            'is_active' => 1,
            'is_delete' => 0,
            'bed_code' => $request->bed_code,
            'bed_name' => $request->bed_name,
            'bed_type_id' => $request->bed_type_id,
            'bed_room_id' => $request->bed_room_id,
            'max_capacity' => $request->max_capacity,
            'is_bed_stretcher' => $request->is_bed_stretcher,
        ]);
        return $data;
    }

    public function updateBed($request, $data, $time, $app_modifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $app_modifier,
            'bed_code' => $request->bed_code,
            'bed_name' => $request->bed_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }

    public function deleteBed($data){
        $data->delete();
        return $data;
    }
}
