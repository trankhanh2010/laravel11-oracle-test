<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class LogController extends BaseApiCacheController
{
    function get_log(Request $request)
    {
        // Kiểm tra param và trả về lỗi nếu nó không hợp lệ
        if ($this->check_param()) {
            return $this->check_param();
        }
        $filePath = storage_path('logs/laravel.log'); // Đường dẫn đến file log

        if (!File::exists($filePath)) {
            return response()->json(['error' => 'File log không tồn tại.'], 404);
        }

        $lines = file($filePath); // Đọc tất cả các dòng trong file log
        $totalLines = count($lines);

        // Kiểm tra nếu start lớn hơn tổng số dòng, đặt start về cuối
        if ($this->start >= $totalLines) {
            $this->start = $totalLines - 1;
        }

        // Sử dụng array_slice để lấy dữ liệu từ vị trí start với số lượng limit
        $logLines = array_slice($lines, $this->start, $this->limit);

        // Chuyển đổi các dòng log thành UTF-8
        $logLines = array_map(function ($line) {
            return mb_convert_encoding($line, 'UTF-8', 'UTF-8');
        }, $logLines);

        // Tạo cấu trúc dữ liệu trả về
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'total_lines' => $totalLines
        ];
        return return_data_success($param_return, $logLines);
    }
}
