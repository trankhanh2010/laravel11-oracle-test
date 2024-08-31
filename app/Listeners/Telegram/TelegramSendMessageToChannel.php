<?php

namespace App\Listeners\Telegram;

use App\Events\Telegram\SendMessageToChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramSendMessageToChannel
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendMessageToChannel $event): void
    {
        $text = $event->mess;
        Telegram::sendMessage([
            'chat_id' => env('TELEGRAM_CHANNEL_ID', ''),
            'parse_mode' => 'HTML',
            'text' => $text
        ]);
    }
}
