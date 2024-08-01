<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\DebateEkipUserResource;
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
            // foreach ($this->order_by as $key => $item) {
            //     if (!in_array($key, $this->order_by_join)) {
            //         if (!$this->debate_ekip_user->getConnection()->getSchemaBuilder()->hasColumn($this->debate_ekip_user->getTable(), $key)) {
            //             unset($this->order_by_request[camelCaseFromUnderscore($key)]);
            //             unset($this->order_by[$key]);
            //         }
            //     }
            // }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->sere_serv_last_id = $this->debate_ekip_user->max('id');
                $this->cursor = $this->sere_serv_last_id;
                $this->equal = "<=";
            }
        }
        if ($this->cursor < 0) {
            $this->sub_order_by = (strtolower($this->order_by["id"]) === 'asc') ? 'desc' : 'asc';
            $this->equal = (strtolower($this->order_by["id"]) === 'desc') ? '>' : '<';

            $this->sub_order_by_string = ' ORDER BY ID ' . $this->order_by["id"];
            $this->cursor = abs($this->cursor);
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
        $keyword = $this->keyword;
        $data = $this->debate_ekip_user
            ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_debate_ekip_user.execute_role_id')
            ->leftJoin('his_department as department', 'department.id', '=', 'his_debate_ekip_user.department_id')
            ->select($select);
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.loginname'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_debate_ekip_user.username'), 'like', $keyword . '%');
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

    public function debate_ekip_user_v2($id = null)
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
        $keyword = $this->keyword;
        try {
            $data = $this->debate_ekip_user
                ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_debate_ekip_user.execute_role_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'his_debate_ekip_user.department_id')
                ->select($select);
            $data_id = $this->debate_ekip_user
                ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_debate_ekip_user.execute_role_id')
                ->leftJoin('his_department as department', 'department.id', '=', 'his_debate_ekip_user.department_id')
                ->select("HIS_DEBATE_EKIP_USER.ID");
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.loginname'), 'like', $keyword . '%');
                        // ->orWhere(DB::connection('oracle_his')->raw('his_debate_ekip_user.username'), 'like', $keyword . '%');
                });
                $data_id = $data_id->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.loginname'), 'like', $keyword . '%');
                        // ->orWhere(DB::connection('oracle_his')->raw('his_debate_ekip_user.username'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_delete'), 0);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_active'), $this->is_active);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.is_active'), $this->is_active);
                });
            }
            if ($this->debate_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.debate_id'), $this->debate_id);
                });
                $data_id = $data_id->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.debate_id'), $this->debate_id);
                });
            }
            if ($this->debate_ekip_user_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_debate_ekip_user.' . $key, $this->sub_order_by ?? $item);
                    }
                }
                // Chuyển truy vấn sang chuỗi sql
                $sql = $data->toSql();
                $sql_id = $data_id->toSql();
                // Truyền tham số qua binding tránh SQL Injection
                $bindings = $data->getBindings();
                $bindings_id = $data_id->getBindings();
                $id_max_sql = DB::connection('oracle_his')->select('SELECT a.ID, ROWNUM  FROM (' . $sql_id . ' order by ID desc) a  WHERE ROWNUM = 1 ', $bindings_id);
                $id_min_sql = DB::connection('oracle_his')->select('SELECT a.ID, ROWNUM  FROM (' . $sql_id . ' order by ID asc) a  WHERE ROWNUM = 1 ', $bindings_id);
                $id_max_sql = intval($id_max_sql[0]->id ?? null);
                $id_min_sql = intval($id_min_sql[0]->id ?? null);
                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . $this->sub_order_by_string . ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = DebateEkipUserResource::collection($data);
                if (isset($data[0])) {
                    if (($data[0]->id != $this->debate_ekip_user->max('id')) && ($data[0]->id != $this->debate_ekip_user->min('id')) && ($data[0]->id != $id_max_sql) && ($data[0]->id != $id_min_sql)) {
                        $this->prev_cursor = '-' . $data[0]->id;
                    } else {
                        $this->prev_cursor = null;
                    }
                    if(((count($data) === 1) && ($this->order_by["id"] == 'desc') && ($data[0]->id == $id_min_sql)) 
                    || ((count($data) === 1) && ($this->order_by["id"] == 'asc') && ($data[0]->id == $id_max_sql))){
                        $this->prev_cursor = '-'.$data[0]->id;
                    }
                    if($this->raw_cursor == 0){
                        $this->prev_cursor = null;
                    }
                    $this->next_cursor = $data[($this->limit - 1)]->id ?? null;
                    if(($this->next_cursor == $id_max_sql && ($this->order_by["id"] == 'asc') ) || ($this->next_cursor == $id_min_sql && ($this->order_by["id"] == 'desc'))){
                        $this->next_cursor = null;
                    }
                }
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_ekip_user.id'), $this->debate_ekip_user_id);
                });
                $data = $data
                    ->first();
            }
            $param_return = [
                'prev_cursor' => $this->prev_cursor ?? null,
                'limit' => $this->limit,
                'next_cursor' => $this->next_cursor ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'debate_ekip_user_id' => $this->debate_ekip_user_id,
                'debate_id' => $this->debate_id,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error();
        }
    }
}
