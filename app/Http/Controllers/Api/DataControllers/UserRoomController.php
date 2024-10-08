<?php

namespace App\Http\Controllers\Api\DataControllers;

use App\Http\Controllers\BaseControllers\BaseApiDataController;
use Illuminate\Http\Request;
use App\Models\HIS\UserRoom;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class UserRoomController extends BaseApiDataController
{

    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->user_room = new UserRoom();

        $this->order_by_join = [];
        // Kiểm tra tên trường trong bảng
        if ($this->order_by != null) {
            $columns = $this->get_columns_table($this->user_room);
            $this->order_by = $this->check_order_by($this->order_by, $columns, $this->order_by_join ?? []);
            $this->order_by_tring = arrayToCustomString($this->order_by);
        }

        $this->equal = ">";
        if ((strtolower($this->order_by["id"] ?? null) == "desc")) {
            $this->equal = "<";
            if ($this->cursor === 0) {
                $this->user_room_last_id = $this->user_room->max('id');
                $this->cursor = $this->user_room_last_id;
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

    public function user_with_room()
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        try {
            // Khai báo các biến lấy từ json param
            $request_loginname = $this->param_request['ApiData']['LOGINNAME'] ?? null;

            // Khai báo các trường cần select
            $select = [
                "id",
                "create_Time",
                "modify_Time",
                "creator",
                "modifier",
                "app_Creator",
                "app_Modifier",
                "is_Active",
                "is_Delete",
                "group_Code",
                "loginname",
                "room_Id",
            ];

            // Khởi tạo, gán các model vào các biến 
            $model = $this->user_room::select($select);

            // Kiểm tra các điều kiện từ json param
            if ($request_loginname != null) {
                $model->where('loginname', $request_loginname);
            }

            // Khai báo các bảng liên kết dùng cho with()
            $param = [
                'room:id,department_id,room_type_id',
                'room.execute_room:id,room_id,execute_room_name,execute_room_code',
                'room.room_type:id,room_type_name,room_type_code',
                'room.department:id,branch_id,department_name,department_code',
                'room.department.branch:id,branch_name,branch_code',
            ];

            // Lấy dữ liệu
            // $count = $model->count();
            $data = $model->skip($this->start)->take($this->limit)->with($param)->get();
            // Trả về dữ liệu
            $param_return = [
                $this->start_name => $this->start,
                $this->limit_name => $this->limit,
                $this->order_by_name => $this->order_by_request
            ];
            return returnDataSuccess($param_return, $data ?? ($data['data'] ?? null));
        } catch (\Throwable $e) {
            // Xử lý lỗi và trả về phản hồi lỗi
            return return500Error($e->getMessage());
        }
    }
}
