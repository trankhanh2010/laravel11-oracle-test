<?php 
namespace App\Repositories;

use App\Models\HIS\ServiceGroup;
use Illuminate\Support\Facades\DB;

class ServiceGroupRepository
{
    protected $serviceGroup;
    public function __construct(ServiceGroup $serviceGroup)
    {
        $this->serviceGroup = $serviceGroup;
    }

    public function applyJoins()
    {
        return $this->serviceGroup
            ->select(
                'his_service_group.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_service_group.service_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_service_group.service_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_service_group.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_service_group.' . $key, $item);
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
        return $this->serviceGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->serviceGroup::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'service_group_code' => $request->service_group_code,
            'service_group_name' => $request->service_group_name,
            'is_public'  => $request->is_public,     
            'num_order' => $request->num_order,
            'parent_service_id'  => $request->parent_service_id,
            'description'  => $request->description,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'service_group_code' => $request->service_group_code,
            'service_group_name' => $request->service_group_name,
            'is_public'  => $request->is_public,     
            'num_order' => $request->num_order,
            'parent_service_id'  => $request->parent_service_id,
            'description'  => $request->description,
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
            $data = $data->where('his_service_group.id','=', $id)->first();
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