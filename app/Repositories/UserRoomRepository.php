<?php 
namespace App\Repositories;

use App\Models\HIS\UserRoom;
use Illuminate\Support\Facades\DB;

class UserRoomRepository
{
    protected $userRoom;
    public function __construct(UserRoom $userRoom)
    {
        $this->userRoom = $userRoom;
    }

    public function applyJoins()
    {
        return $this->userRoom
            ->select(
                'his_user_room.*'
            );
    }
    public function view()
    {
        return $this->userRoom
            ->leftJoin('his_room', 'his_room.id', '=', 'his_user_room.room_id')
            ->leftJoin('his_room_type as room_type', 'room_type.id', '=', 'his_room.room_type_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'his_room.department_id')
            ->leftJoin('his_branch as branch', 'branch.id', '=', 'department.branch_id')
            ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
            ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
            ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
            ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
            ->leftJoin('his_refectory as refectory', 'his_room.id', '=', 'refectory.room_id')
            ->leftJoin('his_sample_room as sample_room', 'his_room.id', '=', 'sample_room.room_id')
            ->leftJoin('his_medi_stock as medi_stock', 'his_room.id', '=', 'medi_stock.room_id')
            ->leftJoin('his_data_store as data_store', 'his_room.id', '=', 'data_store.room_id')
            ->leftJoin('his_station as station', 'his_room.id', '=', 'station.room_id')
            ->select(
                'his_user_room.*',
                'his_room.is_pause',
                'his_room.department_id',
                'his_room.room_type_id',
                'his_room.g_code',
                'room_type.room_type_code',
                'room_type.room_type_name',
                'department.branch_id',
                'department.department_code',
                'department.department_name',
                'branch.branch_code',
                'branch.branch_name',
                'branch.hein_medi_org_code',
                DB::connection('oracle_his')->raw(
            'NVL(bed.bed_room_name, 
            NVL(cashier.cashier_room_name, 
            NVL(execute.execute_room_name, 
            NVL(reception.reception_room_name,
            NVL(refectory.refectory_name,
            NVL(sample_room.sample_room_name,
            NVL(medi_stock.medi_stock_name,
            NVL(data_store.data_store_name,
            station.station_name)))))))) AS room_name'),
                DB::connection('oracle_his')->raw(
            'NVL(bed.bed_room_code, 
            NVL(cashier.cashier_room_code, 
            NVL(execute.execute_room_code, 
            NVL(reception.reception_room_code,
            NVL(refectory.refectory_code,
            NVL(sample_room.sample_room_code,
            NVL(medi_stock.medi_stock_code,
            NVL(data_store.data_store_code,
            station.station_code)))))))) AS room_code')
            )
            ->whereNotNull(DB::connection('oracle_his')->raw(
            'NVL(bed.bed_room_name, 
            NVL(cashier.cashier_room_name, 
            NVL(execute.execute_room_name, 
            NVL(reception.reception_room_name,
            NVL(refectory.refectory_name,
            NVL(sample_room.sample_room_name,
            NVL(medi_stock.medi_stock_name,
            NVL(data_store.data_store_name,
            station.station_name))))))))'));
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_user_room.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_user_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyLoginnameFilter($query, $loginname)
    {
        if ($loginname !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_user_room.loginname'), $loginname);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['is_pause', 'department_id', 'room_type_id', 'g_code'])) {
                        $query->orderBy('his_room.' . $key, $item);
                    }
                    if (in_array($key, ['room_type_code', 'room_type_name'])) {
                        $query->orderBy('room_type.' . $key, $item);
                    }
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['branch_code', 'branch_name', 'hein_medi_org_code'])) {
                        $query->orderBy('branch.' . $key, $item);
                    }
                    if (in_array($key, ['room_name', 'room_code'])) {
                        $query->orderBy($key, $item);
                    }
                } else {
                    $query->orderBy('his_user_room.' . $key, $item);
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
        return $this->userRoom->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->userRoom::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'user_room_code' => $request->user_room_code,
    //         'user_room_name' => $request->user_room_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'user_room_code' => $request->user_room_code,
    //         'user_room_name' => $request->user_room_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($id = null){
        $data = $this->view();
        if($id != null){
            $data = $data->where('his_user_room.id','=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes(); 
            })->toArray(); 
        }
        return $data;
    }
}