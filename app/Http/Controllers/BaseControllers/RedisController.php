<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;
class RedisController extends Controller
{
    public function ping(){
         // Ghi nhận thời gian bắt đầu
    $startTime = Carbon::now();

    try {
        // Thực hiện một lệnh Redis đơn giản để kiểm tra kết nối
        Redis::ping();
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Không thể kết nối Redis: ' . $e->getMessage()
        ], 500);
    }

    // Ghi nhận thời gian kết thúc
    $endTime = Carbon::now();

    // Tính thời gian kết nối
    $elapsedTime = $startTime->diffInMilliseconds($endTime); // Thời gian tính bằng mili giây

    return response()->json([
        'status' => 'Redis connected successfully',
        'elapsed_time_ms' => $elapsedTime . ' ms',
    ]);
    }
}
