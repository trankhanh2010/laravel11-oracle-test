<?php 
namespace App\Repositories;

use App\Models\HIS\BedBsty;
use Illuminate\Support\Facades\DB;

class BedBstyRepository
{
    protected $bedBsty;
    public function __construct(BedBsty $bedBsty)
    {
        $this->bedBsty = $bedBsty;
    }

    public function applyJoins()
    {
        return $this->bedBsty
        ->leftJoin('his_service as service', 'service.id', '=', 'his_bed_bsty.bed_service_type_id')
        ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
        ->leftJoin('his_bed as bed', 'bed.id', '=', 'his_bed_bsty.bed_id')
        ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'bed.bed_room_id')
        ->leftJoin('his_room as room', 'room.id', '=', 'bed_room.room_id')
        ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')

        ->select(
            'his_bed_bsty.*',
            'service.service_name',
            'service.service_code',
            'service_type.service_type_name',
            'service_type.service_type_code',
            'bed.bed_name',
            'bed.bed_code',
            'bed_room.bed_room_name',
            'bed_room.bed_room_code',
            'department.department_name',
            'department.department_code'
        );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_bed_bsty.bed_bsty_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_bed_bsty.bed_bsty_name'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_bed_bsty.is_active'), $isActive);
        }
        return $query;
    }
    public function applyServiceIdsFilter($query, $serviceIds)
    {
        if ($serviceIds != null) {
            $query = $query->whereIn(DB::connection('oracle_his')->raw('his_bed_bsty.bed_service_type_id'), $serviceIds);
        }
        return $query;
    }
    public function applyBedIdsFilter($query, $bedIds)
    {
        if ($bedIds != null) {
            $query = $query->whereIn(DB::connection('oracle_his')->raw('his_bed_bsty.bed_id'), $bedIds);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['service_name', 'service_code'])) {
                        $query->orderBy('service.' . $key, $item);
                    }
                    if (in_array($key, ['service_type_name', 'service_type_code'])) {
                        $query->orderBy('service_type.' . $key, $item);
                    }
                    if (in_array($key, ['bed_name', 'bed_code'])) {
                        $query->orderBy('bed.' . $key, $item);
                    }
                    if (in_array($key, ['bed_room_name', 'bed_room_code'])) {
                        $query->orderBy('bed_room.' . $key, $item);
                    }
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_bed_bsty.' . $key, $item);
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
        return $this->bedBsty->find($id);
    }
    public static function getDataFromDbToElastic($id = null){
        $data = DB::connection('oracle_his')->table('his_bed_bsty')
        ->leftJoin('his_service as service', 'service.id', '=', 'his_bed_bsty.bed_service_type_id')
        ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
        ->leftJoin('his_bed as bed', 'bed.id', '=', 'his_bed_bsty.bed_id')
        ->leftJoin('his_bed_room as bed_room', 'bed_room.id', '=', 'bed.bed_room_id')
        ->leftJoin('his_room as room', 'room.id', '=', 'bed_room.room_id')
        ->leftJoin('his_department as department', 'department.id', '=', 'room.department_id')

        ->select(
            'his_bed_bsty.*',
            'service.service_name',
            'service.service_code',
            'service_type.service_type_name',
            'service_type.service_type_code',
            'bed.bed_name',
            'bed.bed_code',
            'bed_room.bed_room_name',
            'bed_room.bed_room_code',
            'department.department_name',
            'department.department_code'
        );
        if($id != null){
            $data = $data->where('his_bed_bsty.id','=', $id)->first();
        }else{
            $data = $data->get();
        }
        return $data;
    }
}