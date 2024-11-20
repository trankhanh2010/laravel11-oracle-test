<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Throwable $e, Request $request) {
            // Đóng tất cả kết nối
            DB::disconnect();
            // Ghi lỗi vào log
            logError($e);
            // Gửi lỗi qua Telegram 
            sendErrorToTelegram($e);
            // Trả về lỗi theo định dạng 
            return return500Error($e->getMessage());
        });
    }
    public function render($request, Throwable $exception)
    {
        // Xử lý các loại exception khác nếu cần thiết
        return parent::render($request, $exception);
    }
}
