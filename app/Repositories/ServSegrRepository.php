<?php 
namespace App\Repositories;

use App\Models\HIS\ServSegr;
use Illuminate\Support\Facades\DB;

class ServSegrRepository
{
    protected $servSegr;
    public function __construct(ServSegr $servSegr)
    {
        $this->servSegr = $servSegr;
    }

    public function applyJoins()
    {
        return $this->servSegr
        ->leftJoin('his_service as service', 'service.id', '=', 'his_serv_segr.service_id')
        ->leftJoin('his_service_group as service_group', 'service_group.id', '=', 'his_serv_segr.service_group_id')
        ->leftJoin('his_service_type as service_type', 'service_type.id', '=', 'service.service_type_id')
        ->select(
            'his_serv_segr.*',
            'service.service_name',
            'service.service_code',
            'service_type.service_type_name',
            'service_type.service_type_code',
            'service_group.service_group_name',
            'service_group.service_group_code',
        );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('service.service_code'), 'like', $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('service_type.service_type_code'), 'like', $keyword . '%')
            ->orWhere(DB::connection('oracle_his')->raw('service_group.service_group_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_serv_segr.is_active'), $isActive);
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
                    if (in_array($key, ['service_group_name', 'service_group_code'])) {
                        $query->orderBy('service_group.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_serv_segr.' . $key, $item);
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
        return $this->servSegr->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->servSegr::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'serv_segr_code' => $request->serv_segr_code,
            'serv_segr_name' => $request->serv_segr_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'serv_segr_code' => $request->serv_segr_code,
            'serv_segr_name' => $request->serv_segr_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_serv_segr.id','=', $id)->first();
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