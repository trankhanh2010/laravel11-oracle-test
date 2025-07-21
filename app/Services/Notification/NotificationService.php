<?php

namespace App\Services\Notification;

use App\Services\Mail\MailService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService 
{
    protected $mailService;

    public function __construct(
        MailService $mailService,
    )
    {
        $this->mailService = $mailService;
    }

    public function sendDangKyKhamThanhCong($responeMos)
    {
        // Tạo job
        return true;
    }
}
