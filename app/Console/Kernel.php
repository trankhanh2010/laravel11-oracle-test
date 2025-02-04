<?php

namespace App\Console;

use App\Jobs\Momo\CheckPaymentSuccessMoMo;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();

        // Job chạy định kỳ kiểm tra các giao dịch thành công mã = 0 nhưng bị khóa viện phí và vẫn còn mã 1000 trong db
        $schedule->call(function () {
            dispatch(app(CheckPaymentSuccessMoMo::class));
        })->everyFourHours();
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
