<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function updated_activity()
    {
        $activity = Telegram::getUpdates();
        dd($activity);
    }
}
