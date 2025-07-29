<?php

namespace App\Jobs\Zalo;

use App\Services\Zalo\ZaloService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RefreshAccessTokenZalo implements ShouldQueue
{
    use Queueable;
    protected $zaloService;

    /**
     * Create a new job instance.
     */
    public function __construct(){}

    /**
     * Execute the job.
     */
    public function handle(
        ZaloService $zaloService,
    ): void
    {
        // gửi mail
        $this->zaloService = $zaloService;
        $this->zaloService->refreshAccessToken();
    }
}
