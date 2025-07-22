<?php

namespace App\Services\Notification;

use App\Jobs\Guest\SendDangKyKhamThanhCongNotification;
use App\Services\Mail\MailService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $mailService;

    public function __construct(
        MailService $mailService,
    ) {
        $this->mailService = $mailService;
    }

    public function sendDangKyKhamThanhCong($responeMos)
    {
        try {
            dispatch(new SendDangKyKhamThanhCongNotification($responeMos));
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
