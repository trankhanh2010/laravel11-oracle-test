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
        if ($this->checkParam()) {
            return $this->checkParam();
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

        // Kết hợp các dòng log liên tiếp để tạo thành một mục log hoàn chỉnh
        $combinedLogLines = [];
        $currentLog = '';
        $currentStartLine = $this->start; // Chỉ số dòng bắt đầu của lỗi hiện tại

        foreach ($logLines as $index => $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Kiểm tra xem dòng hiện tại có bắt đầu một log mới không
            if (preg_match('/^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]\s+/', $line)) {
                // Nếu có log hiện tại, lưu nó vào mảng và bắt đầu một log mới
                if (!empty($currentLog)) {
                    $combinedLogLines[] = [
                        'log' => $currentLog,
                        'startLine' => $currentStartLine
                    ];
                }
                $currentLog = $line; // Bắt đầu log mới
                $currentStartLine = $this->start + $index; // Cập nhật dòng bắt đầu cho log mới
            } else {
                // Nếu không, nối tiếp vào log hiện tại
                $currentLog .= "\n" . $line;
            }
        }

        // Lưu log cuối cùng
        if (!empty($currentLog)) {
            $combinedLogLines[] = [
                'log' => $currentLog,
                'startLine' => $currentStartLine
            ];
        }

        if ($this->line === null) {
            // Khi $this->line là null, chỉ trả về thông tin ngày tháng và text log
            $logEntries = array_map(function ($logItem) {
                $log = $logItem['log'];
                $startLine = $logItem['startLine'];

                $logEntry = [
                    'timestamp' => '',
                    'api' => '',
                    'loginname' => '',
                    'hostname' => '',
                    'ip' => '',
                    'log_text' => '',
                    'detail' => '...',
                    'start_line' => $startLine
                ];

                // Tách ngày tháng và phần còn lại của log
                if (preg_match('/^\[(.*?)\]\s+(.*)$/s', $log, $matches)) {
                    $logEntry['timestamp'] = $matches[1];
                    $remainingText = $matches[2];

                    // Tìm phần JSON bắt đầu từ dấu { đầu tiên đến hết dòng
                    $jsonStartPos = strpos($remainingText, '{');
                    if ($jsonStartPos !== false) {
                        // Phần text trước dấu {
                        $logEntry['log_text'] = trim(substr($remainingText, 0, $jsonStartPos));
                        // Api
                        $pattern = '/Api:\s*(.*?)\s*; Loginame:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['api'] = $matches[1] ?? null;
                        // Loginname
                        $pattern = '/Loginame:\s*(.*?)\s*; Hostname:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['loginname'] = $matches[1] ?? null;
                        // Hostname
                        $pattern = '/Hostname:\s*(.*?)\s*; IP máy:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['hostname'] = $matches[1] ?? null;
                        // Ip
                        $pattern = '/IP máy:\s*(.*?)\s*; Mô tả:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['ip'] = $matches[1] ?? null;

                        if($logEntry['api'] != null || $logEntry['loginname'] != null || $logEntry['hostname'] != null || $logEntry['ip'] != null){
                            // Info
                            $pattern = '/Mô tả:\s*(.*)$/s';
                            preg_match($pattern, $logEntry['log_text'], $matches);
                            $logEntry['log_text'] = $matches[1] ?? null;
                        }
                    } else {
                        // Nếu không có dấu {, giữ nguyên toàn bộ văn bản
                        $logEntry['log_text'] = $remainingText;
                    }
                } else {
                    // Nếu không tìm thấy ngày tháng, giữ nguyên dòng log
                    $logEntry['log_text'] = $log;
                }

                return $logEntry;
            }, $combinedLogLines);
        } else {
            // Khi $this->line có giá trị, chỉ lấy log tại dòng cụ thể
            $logEntries = array_filter($combinedLogLines, function ($logItem) {
                return $logItem['startLine'] === $this->line;
            });

            $logEntries = array_map(function ($logItem) {
                $log = $logItem['log'];
                $startLine = $logItem['startLine'];

                $logEntry = [
                    'timestamp' => '',
                    'api' => '',
                    'loginname' => '',
                    'hostname' => '',
                    'ip' => '',
                    'log_text' => '',
                    'message' => '',
                    'file' => '',
                    'line' => '',
                    'trace' => '',
                    'url' => '',
                    'request_data' => '',
                    'detail' => '...',
                    'start_line' => $startLine
                ];

                // Tách ngày tháng và phần còn lại của log
                if (preg_match('/^\[(.*?)\]\s+(.*)$/s', $log, $matches)) {
                    $logEntry['timestamp'] = $matches[1];
                    $remainingText = $matches[2];

                    // Tìm phần JSON bắt đầu từ dấu { đầu tiên đến hết dòng
                    $jsonStartPos = strpos($remainingText, '{');
                    if ($jsonStartPos !== false) {
                        // Phần text trước dấu {
                        $logEntry['log_text'] = trim(substr($remainingText, 0, $jsonStartPos));
                        // Api
                        $pattern = '/Api:\s*(.*?)\s*; Loginame:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['api'] = $matches[1] ?? null;
                        // Loginname
                        $pattern = '/Loginame:\s*(.*?)\s*; Hostname:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['loginname'] = $matches[1] ?? null;
                        // Hostname
                        $pattern = '/Hostname:\s*(.*?)\s*; IP máy:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['hostname'] = $matches[1] ?? null;
                        // Ip
                        $pattern = '/IP máy:\s*(.*?)\s*; Mô tả:/';
                        preg_match($pattern, $logEntry['log_text'], $matches);
                        $logEntry['ip'] = $matches[1] ?? null;

                        if($logEntry['api'] != null || $logEntry['loginname'] != null || $logEntry['hostname'] != null || $logEntry['ip'] != null){
                            // Info
                            $pattern = '/Mô tả:\s*(.*)$/s';
                            preg_match($pattern, $logEntry['log_text'], $matches);
                            $logEntry['log_text'] = $matches[1] ?? null;
                        }
                        // Phần detail từ dấu { trở đi
                        $logEntry['detail'] = trim(substr($remainingText, $jsonStartPos));
                        // Lấy message
                        $pattern = '/{"message":\s*"([^"]*)"\s*,\s*"file"/';
                        preg_match($pattern, $logEntry['detail'], $matches);
                        $logEntry['message'] = $matches[1] ?? null;
                        // Lấy file
                        $pattern = '/"file":\s*"([^"]*)"\s*,\s*"line"/';
                        preg_match($pattern, $logEntry['detail'], $matches);
                        $logEntry['file'] = $matches[1] ?? null;
                        // Lấy line
                        $pattern = '/"line":\s*"([^"]*)"\s*,\s*"trace"/';
                        preg_match($pattern, $logEntry['detail'], $matches);
                        $logEntry['line'] = $matches[1] ?? null;
                        // Lấy trace
                        $pattern = '/"trace":\s*"([^"]*)"\s*,\s*"url"/';
                        preg_match($pattern, $logEntry['detail'], $matches);
                        $logEntry['trace'] = $matches[1] ?? null;
                        // Lấy url
                        $pattern = '/"url":\s*"([^"]*)"\s*,\s*"request_data"/';
                        preg_match($pattern, $logEntry['detail'], $matches);
                        $logEntry['url'] = $matches[1] ?? null;
                        // Lấy data
                        $pattern = '/"request_data"\s*(.*)$/s';
                        preg_match($pattern, $logEntry['detail'], $matches);
                        $logEntry['request_data'] = $matches[1] ?? null;
                    } else {
                        // Nếu không có dấu {, giữ nguyên toàn bộ văn bản
                        $logEntry['log_text'] = $remainingText;
                        $logEntry['detail'] = null; // Không có JSON
                    }
                } else {
                    // Nếu không tìm thấy ngày tháng, giữ nguyên dòng log
                    $logEntry['log_text'] = $log;
                    $logEntry['detail'] = null; // Không có JSON
                }

                return $logEntry;
            }, $logEntries);
        }

        // Tạo cấu trúc dữ liệu trả về
        $param_return = [
            'start' => $this->start,
            'limit' => $this->limit,
            'total_lines' => $totalLines
        ];
        return returnDataSuccess($param_return, $logEntries);
    }
}
