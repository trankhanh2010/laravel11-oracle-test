<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\DebateEkipUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebateEkipUserController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->debate_ekip_user = new DebateEkipUser();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->debate_ekip_user->getConnection()->getSchemaBuilder()->hasColumn($this->debate_ekip_user->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function debate_ekip_user($id = null)
    {
        $select = [
            'his_debate_ekip_user.*',
            'execute_role.execute_role_code',
            'execute_role.execute_role_name',
            'department.department_code',
            'department.department_name'
        ];
        $param = [
            'execute_role:id,execute_role_name,execute_role_code',
            'department:id,department_name,department_code'
        ];
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        $data = $this->debate_ekip_user
            ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_debate_ekip_user.execute_role_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'his_debate_ekip_user.department_id')
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('lower(his_debate_ekip_user.loginname)'), 'like', '%' . $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('lower(his_debate_ekip_user.username)'), 'like', '%' . $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_active'), $this->is_active);
            });
        }
        if ($this->debate_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.debate_id'), $this->debate_id);
            });
        }
        if ($this->debate_ekip_user_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_debate_ekip_user.' . $key, $item);
                }
            }
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.id'), $this->debate_ekip_user_id);
            });
            $data = $data
                ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'debate_ekip_user_id' => $this->debate_ekip_user_id,
            'debate_id' => $this->debate_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }
}
