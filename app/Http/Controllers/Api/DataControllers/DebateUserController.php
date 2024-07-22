<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Http\Resources\DebateUserResource;
use App\Models\HIS\DebateUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DebateUserController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->debate_user = new DebateUser();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            foreach ($this->order_by as $key => $item) {
                if (!in_array($key, $this->order_by_join)) {
                    if (!$this->debate_user->getConnection()->getSchemaBuilder()->hasColumn($this->debate_user->getTable(), $key)) {
                        unset($this->order_by_request[camelCaseFromUnderscore($key)]);
                        unset($this->order_by[$key]);
                    }
                }
            }
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->debate_user_last_id = $this->debate_user->max('id');
                $this->cursor = $this->debate_user_last_id;
                $this->equal = "<=";
            }
        }
    }
    public function debate_user()
    {
        $param = [];
        $keyword = $this->keyword;
        $data = $this->debate_user;
        if ($keyword != null) {
            $data = $data->where(function ($query) use ($keyword) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.loginname'), 'like', $keyword . '%')
                    ->orWhere(DB::connection('oracle_his')->raw('his_debate_user.username'), 'like', $keyword . '%');
            });
        }
        if (!$this->is_include_deleted) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.is_delete'), 0);
            });
        }
        if ($this->is_active !== null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.is_active'), $this->is_active);
            });
        }
        if ($this->debate_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.debate_id'), $this->debate_id);
            });
        }
        if ($this->debate_user_id == null) {
            $count = $data->count();
            if ($this->order_by != null) {
                foreach ($this->order_by as $key => $item) {
                    $data->orderBy('his_debate_user.' . $key, $item);
                }
            }
            $data = $data->with($param);
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->get();
        } else {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.id'), $this->debate_user_id);
            });
            $data = $data->with($param);
            $data = $data
                ->skip($this->start)
                ->take($this->limit)
                ->first();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted ?? false,
            'is_active' => $this->is_active,
            'debate_user_id' => $this->debate_user_id,
            'debate_id' => $this->debate_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }

    public function debate_user_v2()
    {
        $param = [];
        $keyword = $this->keyword;
        try {
            $data = $this->debate_user;
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.loginname'), 'like', $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('his_debate_user.username'), 'like', $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.is_active'), $this->is_active);
                });
            }
            if ($this->debate_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.debate_id'), $this->debate_id);
                });
            }
            if ($this->debate_user_id == null) {
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_debate_user.' . $key, $item);
                    }
                }
                // Chuyển truy vấn sang chuỗi sql
                $sql = $data->toSql();
                // Truyền tham số qua binding tránh SQL Injection
                $bindings = $data->getBindings();
                $fullSql = 'SELECT * FROM (SELECT a.*, ROWNUM rnum FROM (' . $sql . ') a WHERE ROWNUM <= ' . ($this->limit + $this->start) . ' AND ID ' . $this->equal . $this->cursor . ') WHERE rnum > ' . $this->start;
                $data = DB::connection('oracle_his')->select($fullSql, $bindings);
                $data = DebateUserResource::collection($data);
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.id'), $this->debate_user_id);
                });
                $data = $data->with($param);
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->first();
            }
            $param_return = [
                'cursor' => $data[0]->id ?? null,
                'limit' => $this->limit,
                'next_cursor' => $data[($this->limit - 1)]->id ?? null,
                'is_include_deleted' => $this->is_include_deleted ?? false,
                'is_active' => $this->is_active,
                'debate_user_id' => $this->debate_user_id,
                'debate_id' => $this->debate_id,
                'keyword' => $this->keyword,
                'order_by' => $this->order_by_request
            ];
            return return_data_success($param_return, $data);
        } catch (\Exception $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_param_error();
        }
    }
}
