<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\HIS\Department;
use App\Models\HIS\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RoomController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->room = new Room();
        $this->department = new Department();
    }
    public function room()
    {
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if($keyword != null){
            $data = DB::connection('oracle_his')->table('his_room')
                    ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                    ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                    ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                    ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                    ->select(
                        'his_room.id',
                        'his_room.department_id',
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        reception.reception_room_name))) AS "room_name"'),
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                        NVL(cashier.cashier_room_code, 
                        NVL(execute.execute_room_code, 
                        reception.reception_room_code))) AS "room_code"')
                    )
                    ->where(function ($query) use ($keyword) {
                        $query->where(DB::connection('oracle_his')->raw('lower(bed.bed_room_name)'), 'like', '%' . $keyword . '%')
                              ->orWhere(DB::connection('oracle_his')->raw('lower(cashier.cashier_room_name)'), 'like', '%' . $keyword . '%')
                              ->orWhere(DB::connection('oracle_his')->raw('lower(execute.execute_room_name)'), 'like', '%' . $keyword . '%')
                              ->orWhere(DB::connection('oracle_his')->raw('lower(reception.reception_room_name)'), 'like', '%' . $keyword . '%');
                    })
                    ->orWhere(function ($query) use ($keyword) {
                        $query->where(DB::connection('oracle_his')->raw('lower(bed.bed_room_code)'), 'like', '%' . $keyword . '%')
                              ->orWhere(DB::connection('oracle_his')->raw('lower(cashier.cashier_room_code)'), 'like', '%' . $keyword . '%')
                              ->orWhere(DB::connection('oracle_his')->raw('lower(execute.execute_room_code)'), 'like', '%' . $keyword . '%')
                              ->orWhere(DB::connection('oracle_his')->raw('lower(reception.reception_room_code)'), 'like', '%' . $keyword . '%');
                    })
                    ->orderBy(DB::connection('oracle_his')->raw('"room_name"'), 'asc');
                $count = $data->count();
                $data = $data    
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
        }else{
            $data = Cache::remember('room_name_code_with_bed_room_bed_room_execute_room_reception_room' . '_start_' . $this->start . '_limit_' . $this->limit, $this->time, function () {
                $data = DB::connection('oracle_his')->table('his_room')
                    ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                    ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                    ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                    ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                    ->select(
                        'his_room.id',
                        'his_room.department_id',
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                        NVL(cashier.cashier_room_name, 
                        NVL(execute.execute_room_name, 
                        reception.reception_room_name))) AS "room_name"'),
                        DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                        NVL(cashier.cashier_room_code, 
                        NVL(execute.execute_room_code, 
                        reception.reception_room_code))) AS "room_code"')
                    )
                    ->whereNotNull('bed.bed_room_name')
                    ->orWhereNotNull('cashier.cashier_room_name')
                    ->orWhereNotNull('execute.execute_room_name')
                    ->orWhereNotNull('reception.reception_room_name')
                    ->orderBy(DB::connection('oracle_his')->raw('"room_name"'), 'asc');
                    $count = $data->count();
                    $data = $data    
                        ->skip($this->start)
                        ->take($this->limit)
                        ->get();
                return ['data' => $data, 'count' => $count];
            });
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? $data['count']
        ];
        return return_data_success($param_return, $data ?? $data['data']);
    }
    public function room_with_department($id)
    {
        if (!is_numeric($id)) {
            return return_id_error($id);
        }
        $data = $this->department->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        $data = Cache::remember('room_name_code_with_bed_room_bed_room_execute_room_reception_room_with_department_id_' . $id, $this->time, function () use ($id) {
            return DB::connection('oracle_his')->table('his_room')
                ->leftJoin('his_bed_room as bed', 'his_room.id', '=', 'bed.room_id')
                ->leftJoin('his_cashier_room as cashier', 'his_room.id', '=', 'cashier.room_id')
                ->leftJoin('his_execute_room as execute', 'his_room.id', '=', 'execute.room_id')
                ->leftJoin('his_reception_room as reception', 'his_room.id', '=', 'reception.room_id')
                ->select(
                    'his_room.id',
                    'his_room.department_id',
                    DB::connection('oracle_his')->raw('NVL(bed.bed_room_name, 
                NVL(cashier.cashier_room_name, 
                NVL(execute.execute_room_name, 
                reception.reception_room_name))) AS "room_name"'),
                    DB::connection('oracle_his')->raw('NVL(bed.bed_room_code, 
                NVL(cashier.cashier_room_code, 
                NVL(execute.execute_room_code, 
                reception.reception_room_code))) AS "room_code"')
                )
                ->where(function ($query) {
                    $query->whereNotNull('bed.bed_room_name')
                        ->orWhereNotNull('cashier.cashier_room_name')
                        ->orWhereNotNull('execute.execute_room_name')
                        ->orWhereNotNull('reception.reception_room_name');
                })
                ->where('his_room.department_id', $id)
                ->get();
        });
        $count = $data->count();
        $param_return = [
            'start' => null,
            'limit' => null,
            'count' => $count
        ];
        return return_data_success($param_return, $data);
    }
}
