<?php

namespace App\Jobs;

use App\Events\Telegram\SendMessageToChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        event(new SendMessageToChannel($this->message));
    }
}
