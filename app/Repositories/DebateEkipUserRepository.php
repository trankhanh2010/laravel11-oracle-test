<?php 
namespace App\Repositories;

use App\Models\HIS\DebateEkipUser;
use Illuminate\Support\Facades\DB;

class DebateEkipUserRepository
{
    protected $debateEkipUser;
    public function __construct(DebateEkipUser $debateEkipUser)
    {
        $this->debateEkipUser = $debateEkipUser;
    }

    public function applyJoins()
    {
        return $this->debateEkipUser
        ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_debate_ekip_user.execute_role_id')
        ->leftJoin('his_department as department', 'department.id', '=', 'his_debate_ekip_user.department_id')
            ->select(
                'his_debate_ekip_user.*',
                'execute_role.execute_role_code',
                'execute_role.execute_role_name',
                'department.department_code',
                'department.department_name'
                );
    }
    public function view()
    {
        return $this->debateEkipUser
        ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_debate_ekip_user.execute_role_id')
        ->leftJoin('his_department as department', 'department.id', '=', 'his_debate_ekip_user.department_id')
            ->select(
                'his_debate_ekip_user.*',
                'execute_role.execute_role_code',
                'execute_role.execute_role_name',
                'department.department_code',
                'department.department_name'
                );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDebateIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.debate_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['execute_role_code', 'execute_role_name'])) {
                        $query->orderBy('execute_role.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_debate_ekip_user.' . $key, $item);
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
        return $this->debateEkipUser->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->debateEkipUser::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'debate_ekip_user_code' => $request->debate_ekip_user_code,
    //         'debate_ekip_user_name' => $request->debate_ekip_user_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'debate_ekip_user_code' => $request->debate_ekip_user_code,
    //         'debate_ekip_user_name' => $request->debate_ekip_user_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($callback, $batchSize = 5000, $id = null)
    {
        $query = $this->applyJoins();
        if ($id != null) {
            $data = $query ->where('his_debate_ekip_user.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data ;
            }
        } else {
            $batchData = [];
            $count = 0;
            foreach ($query->cursor() as $item) {
                $attributes = $item->getAttributes();
                $batchData[] = $attributes;
                $count++;
                
                if ($count % $batchSize == 0) {
                    $callback($batchData);
                    $batchData = [];
                }
            }
            if (!empty($batchData)) {
                $callback($batchData);
            }
        }
    }
}