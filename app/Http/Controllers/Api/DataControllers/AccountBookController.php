<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use App\Models\HIS\AccountBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AccountBookController extends BaseApiDataController
{
    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->account_book = new AccountBook();
        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->account_book);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }
    }

    public function account_book_get_view(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if($this->check_param()){
            return $this->check_param();
        }

        $select = [
            "his_account_book.ID",
            "his_account_book.CREATE_TIME",
            "his_account_book.MODIFY_TIME",
            "his_account_book.CREATOR",
            "his_account_book.MODIFIER",
            "his_account_book.APP_CREATOR",
            "his_account_book.APP_MODIFIER",
            "his_account_book.IS_ACTIVE",
            "his_account_book.IS_DELETE",
            "his_account_book.ACCOUNT_BOOK_CODE",
            "his_account_book.ACCOUNT_BOOK_NAME",
            "his_account_book.TOTAL",
            "his_account_book.FROM_NUM_ORDER",
            "his_account_book.IS_FOR_DEPOSIT",
            "his_account_book.IS_FOR_REPAY",
            "his_account_book.NUM_ORDER",
            "his_account_book.BILL_TYPE_ID",
            "his_account_book.MAX_ITEM_NUM_PER_TRANS",
            "V_HIS_ACCOUNT_BOOK.CURRENT_NUM_ORDER",
        ];
        $param = [];

        $keyword = $this->keyword;
        try {
            $data = $this->account_book
            ->leftJoin('V_HIS_ACCOUNT_BOOK ', 'his_account_book.id', '=', 'V_HIS_ACCOUNT_BOOK.id')
            ->leftJoin('his_user_account_book ', 'his_account_book.id', '=', 'his_user_account_book.account_book_id')
            ->leftJoin('his_caro_account_book ', 'his_account_book.id', '=', 'his_caro_account_book.account_book_id')
                ->select($select);
            if ($keyword != null) {
            }
            if (!$this->is_include_deleted) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.is_delete'), 0);
                });
            }
            if ($this->is_active !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.is_active'), $this->is_active);
                });
            }
            if ($this->is_out_of_bill !== null) {
                if (!$this->is_out_of_bill) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.is_for_bill'), '=', 1);
                    });
                } else {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.is_for_bill'), '!=', 1);
                    });
                }
            }
            if ($this->for_deposit !== null) {
                if ($this->for_deposit) {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.is_for_deposit'), '=', 1);
                    });
                } else {
                    $data = $data->where(function ($query) {
                        $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.is_for_deposit'), '!=', 1);
                    });
                }
            }
            if ($this->loginname !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_user_account_book.loginname'), $this->loginname);
                });
            }
            if ($this->cashier_room_id !== null) {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_caro_account_book.cashier_room_id'), $this->cashier_room_id);
                });
            }
            if ($this->account_book_id == null) {
                $count = $data->count();
                if ($this->order_by != null) {
                    foreach ($this->order_by as $key => $item) {
                        $data->orderBy('his_account_book.' . $key, $item);
                    }
                }
                $data = $data->with($param);
                $data = $data
                    ->skip($this->start)
                    ->take($this->limit)
                    ->get();
            } else {
                $data = $data->where(function ($query) {
                    $query = $query->where(DB::connection('oracle_his')->raw('his_account_book.id'), $this->account_book_id);
                });
                $data = $data
                    ->first();
            }
            $param_return = [
                $this->start_name => $this->start,
                $this->limit_name => $this->limit,
                $this->count_name => $count ?? null,
                $this->is_include_deleted_name => $this->is_include_deleted ?? false,
                $this->is_active_name => $this->is_active,
                $this->account_book_id_name => $this->account_book_id,
                $this->is_out_of_bill_name => $this->is_out_of_bill,
                $this->for_deposit_name => $this->for_deposit,
                $this->loginname_name => $this->loginname,
                $this->cashier_room_id_name => $this->cashier_room_id,
                $this->keyword_name => $this->keyword,
                $this->order_by_name => $this->order_by_request
            ];
            return returnDataSuccess($param_return, $data);
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return500Error($e->getMessage());
        }
    }
}
