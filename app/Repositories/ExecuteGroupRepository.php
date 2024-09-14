<?php 
namespace App\Repositories;

use App\Models\HIS\ExecuteGroup;
use Illuminate\Support\Facades\DB;

class ExecuteGroupRepository
{
    protected $executeGroup;
    public function __construct(ExecuteGroup $executeGroup)
    {
        $this->executeGroup = $executeGroup;
    }

    public function applyJoins()
    {
        return $this->executeGroup
            ->select(
                'his_execute_group.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_group.execute_group_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_execute_group.execute_group_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_group.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_execute_group.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        $param = [
            'debate_ekip_users:id,debate_id,loginname,username,execute_role_id',
            'debate_invite_users:id,debate_id,loginname,username',
            'debate_users:id,debate_id,loginname,username',
            'ekip_plan_users:id,execute_role_id,loginname,username',
            'ekip_temp_users:id,execute_role_id,loginname,username',
            'execute_role_users:id,execute_role_id,loginname',
            'exp_mest_users:id,execute_role_id,loginname,username',
            'imp_mest_users:id,execute_role_id,loginname,username',
            'imp_user_temp_dts:id,execute_role_id,loginname,username',
            'mest_inve_users:id,execute_role_id,loginname,username',
            'remunerations:id,execute_role_id,service_id,price,execute_loginname,execute_username',
            'surg_remu_details:id,execute_role_id,group_code,price,surg_remuneration_id',
            'user_group_temp_dts:id,execute_role_id,group_code,user_group_temp_id,loginname,username,description'
        ];
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
        return $this->executeGroup->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->executeGroup::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'execute_group_code' => $request->execute_group_code,
            'execute_group_name' => $request->execute_group_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'execute_group_code' => $request->execute_group_code,
            'execute_group_name' => $request->execute_group_name,
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
            $data = $data->where('his_execute_group.id','=', $id)->first();
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