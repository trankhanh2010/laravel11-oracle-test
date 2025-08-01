<?php

namespace App\Console;

use App\Console\Commands\BlockMigrateRefresh;
use App\Jobs\Momo\CheckPaymentSuccessMoMo;
use App\Jobs\Zalo\RefreshAccessTokenZalo;
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
        BlockMigrateRefresh::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Job chạy định kỳ mỗi ngày để refresh lại AccessToken Zalo
        $schedule->call(function () {
            dispatch(new RefreshAccessTokenZalo);
        })->dailyAt('12:00');
        
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
