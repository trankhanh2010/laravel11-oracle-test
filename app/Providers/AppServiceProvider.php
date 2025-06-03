<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('rgb_color', function ($attribute, $value, $parameters, $validator) {
            // Kiểm tra xem $value có phải là chuỗi không
            if (!is_string($value)) {
                return false;
            }
            // Tách các giá trị bằng dấu phẩy
            $parts = explode(',', $value);

            // Kiểm tra xem có đúng 3 phần tử không
            if (count($parts) !== 3) {
                return false;
            }
            // Kiểm tra từng phần tử xem có phải là số nguyên từ 0 đến 255 hay không
            foreach ($parts as $part) {
                if (!ctype_digit($part) || (int)$part < 0 || (int)$part > 255) {
                    return false;
                }
            }
            return true;
        });
        // Tùy chọn: Thêm thông báo lỗi tùy chỉnh
        Validator::replacer('rgb_color', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, ':attribute phải là mã màu RGB hợp lệ (ví dụ: 255,192,192)!');
        });

        // Tùy chỉnh để Migration chạy các file trong thư mục phụ
        $this->loadMigrationsFrom([
            database_path('migrations'),
            database_path('migrations/His/Views'),
            database_path('migrations/Emr/Views'),
        ]);
    }
}
