<?php

namespace App\Jobs\Zalo;

use App\Services\Mail\MailService;
use App\Services\Zalo\ZaloService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendInvalidRefreshTokenTokenZaloNotification implements ShouldQueue
{
    use Queueable;
    protected $AT;
    protected $RT;
    protected $mailService;

    /**
     * Create a new job instance.
     */
    public function __construct($AT, $RT)
    {
        $this->AT = $AT;
        $this->RT = $RT;
    }

    /**
     * Execute the job.
     */
    public function handle(
        MailService $mailService,
    ): void
    {
        // gửi mail
        $this->mailService = $mailService;
        $this->mailService->sendInvalidRefreshTokenTokenZaloNotification($this->AT, $this->RT);
    }
}
