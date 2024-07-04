<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
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
    }
    public function debate_user()
    {
        $param = [
        ];
        $keyword = mb_strtolower($this->keyword, 'UTF-8');
        if (($this->debate_user_id == null) && (($keyword != null) || (!$this->is_include_deleted) || ($this->debate_id != null))) {
            $data = $this->debate_user;
            if ($keyword != null) {
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query->where(DB::connection('oracle_his')->raw('lower(his_debate_user.loginname)'), 'like', '%' . $keyword . '%')
                        ->orWhere(DB::connection('oracle_his')->raw('lower(his_debate_user.username)'), 'like', '%' . $keyword . '%');
                });
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.is_delete'), 0);
                });
            }
            if ($this->debate_id != null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.debate_id'), $this->debate_id);
                });
            }

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
        }else{
            $data = $this->debate_user;
            if ($this->debate_user_id != null) {
            $data = $data->where(function ($query) {
                $query = $query->where(DB::connection('oracle_his')->raw('his_debate_user.id'), $this->debate_user_id);
            });
        }
        $data = $data->with($param);
        $data = $data
            ->skip($this->start)
            ->take($this->limit)
            ->get();
        }
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'count' => $count ?? null,
            'is_include_deleted' => $this->is_include_deleted,
            'debate_user_id' => $this->debate_user_id,
            'debate_id' => $this->debate_id,
            'keyword' => $this->keyword,
            'order_by' => $this->order_by_request
        ];
        return return_data_success($param_return, $data);
    }
}
