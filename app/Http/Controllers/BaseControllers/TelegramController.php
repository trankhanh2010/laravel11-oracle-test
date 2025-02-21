<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Services\Telegram\TelegramService;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    protected $telegramService;
    public function __construct(
        TelegramService $telegramService,
    ) {
        $this->telegramService = $telegramService;
    }
    public function updated_activity()
    {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
    public function testSendMessToChanelTelegram()
    {
        $response = $this->telegramService->sendMessage("Hello, đang gửi thử tin nhắn.");
    }
}
