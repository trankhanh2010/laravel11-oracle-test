<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\Events\Cache\DeleteCache;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Supplier\CreateSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Models\HIS\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SupplierController extends BaseApiCacheController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->supplier = new Supplier();

        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->supplier);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }
    public function supplier($id = null)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            $keyword = $this->keyword;
            if ($keyword != null) {
                $data = $this->supplier
                    ->select(
                        'his_supplier.*',
                    );
                $data = $data->where(function ($query) use ($keyword) {
                    $query = $query
                        ->where(DB::connection('oracle_his')->raw('his_supplier.supplier_code'), 'like', $keyword . '%');
                });
                if ($this->is_active !== null) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_supplier.is_active'), $this->is_active);
                    });
                }
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_supplier.' . $key, $item);
                    }
                }
                if($this->get_all){
                    $data = $data
                    ->get();
                }else{
                    $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
                }
            } else {
                if ($id == null) {
                    $data = Cache::remember($this->supplier_name . '_start_' . $this->start . '_limit_' . $this->limit . $this->order_by_tring . '_is_active_' . $this->is_active. '_get_all_' . $this->get_all, $this->time, function () {
                        $data = $this->supplier
                        ->select(
                            'his_supplier.*',
                        );
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_supplier.is_active'), $this->is_active);
                            });
                        }

                        $count = $data->count();
                        if ($this->order_by != null) {
                            foreach ($this->order_by as $key => $item) {
                                $data->orderBy('his_supplier.' . $key, $item);
                            }
                        }
                        if($this->get_all){
                            $data = $data
                            ->get();
                        }else{
                            $data = $data
                            ->skip($this->start)
                            ->take($this->limit)
                            ->get();
                        }
                        return ['data' => $data, 'count' => $count];
                    });
                } else {
                    if (!is_numeric($id)) {
                        return returnIdError($id);
                    }
                    $check_id = $this->check_id($id, $this->supplier, $this->supplier_name);
                    if($check_id){
                        return $check_id; 
                    }
                    $data = Cache::remember($this->supplier_name . '_' . $id . '_is_active_' . $this->is_active, $this->time, function () use ($id) {
                        $data = $this->supplier
                        ->select(
                            'his_supplier.*',
                        )
                            ->where('his_supplier.id', $id);
                        if ($this->is_active !== null) {
                            $data = $data->where(function ($query) {
                                $query = $query->where(DB::connection('oracle_his')->raw('his_supplier.is_active'), $this->is_active);
                            });
                        }
                        $data = $data->first();
                        return $data;
                    });
                }
            }
            $param_return = [
                $this->get_all_name => $this->get_all,
                $this->start_name => ($this->get_all || !is_null($id)) ? null : $this->start,
                $this->limit_name => ($this->get_all || !is_null($id)) ? null : $this->limit,
                $this->count_name => $count ?? ($data['count'] ?? null),
                $this->is_active_name => $this->is_active,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return return_data_success($param_return, $data ?? ($data['data'] ?? null) ?? null);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
    // /// Supplier
    // public function supplier($id = null)
    // {
    //     if ($id == null) {
    //         $name = $this->supplier_name;
    //         $param = [];
    //     } else {
    //         $name = $this->supplier_name . '_' . $id;
    //         $param = [];
    //     }
    //     $data = get_cache_full($this->supplier, $param, $name, $id, $this->time);
    //     return response()->json(['data' => $data], 200);
    // }
    public function supplier_create(CreateSupplierRequest $request)
    {
        try {
            $data = $this->supplier::create([
                'create_time' => now()->format('Ymdhis'),
                'modify_time' => now()->format('Ymdhis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $this->time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_creator' => $this->app_creator,
                'app_modifier' => $this->app_modifier,
                'is_active' => 1,
                'is_delete' => 0,

                'supplier_code' => $request->supplier_code,
                'supplier_name' => $request->supplier_name,
                'supplier_short_name' => $request->supplier_short_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'tax_code' => $request->tax_code,

                'representative' => $request->representative,
                'position' => $request->position,
                'auth_letter_num' => $request->auth_letter_num,
                'auth_letter_issue_date' => $request->auth_letter_issue_date,
                'contract_num' => $request->contract_num,
                'contract_date' => $request->contract_date,

                'bank_account' => $request->bank_account,
                'fax' => $request->fax,
                'bank_info' => $request->bank_info,
                'address' => $request->address,

            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->supplier_name));
            return return_data_create_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }
                 
    public function supplier_update(UpdateSupplierRequest $request, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->supplier->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->update([
                'modify_time' => now()->format('Ymdhis'),
                'modifier' => get_loginname_with_token($request->bearerToken(), $this->time),
                'app_modifier' => $this->app_modifier,

                'supplier_code' => $request->supplier_code,
                'supplier_name' => $request->supplier_name,
                'supplier_short_name' => $request->supplier_short_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'tax_code' => $request->tax_code,

                'representative' => $request->representative,
                'position' => $request->position,
                'auth_letter_num' => $request->auth_letter_num,
                'auth_letter_issue_date' => $request->auth_letter_issue_date,
                'contract_num' => $request->contract_num,
                'contract_date' => $request->contract_date,

                'bank_account' => $request->bank_account,
                'fax' => $request->fax,
                'bank_info' => $request->bank_info,
                'address' => $request->address,

                'is_active' => $request->is_active
            ]);
            // Gọi event để xóa cache
            event(new DeleteCache($this->supplier_name));
            return return_data_update_success($data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_500_error($e->getMessage());
        }
    }

    public function supplier_delete(Request $request, $id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->supplier->find($id);
        if ($data == null) {
            return return_not_record($id);
        }
        try {
            $data->delete();
            // Gọi event để xóa cache
            event(new DeleteCache($this->supplier_name));
            return return_data_delete_success();
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return_data_delete_fail();
        }
    }
}
