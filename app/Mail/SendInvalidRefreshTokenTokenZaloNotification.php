<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendInvalidRefreshTokenTokenZaloNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $AT;
    public $RT;

    public function __construct($AT, $RT)
    {
        $this->AT = $AT;
        $this->RT = $RT;
    }

    public function build()
    {
        return $this->subject(config('app')['name'].': Invalid Refresh Token Zalo')
            ->view('emails.send_invalid_refresh_token_token_zalo_notification')
            ->with([
                'AT' => $this->AT,
                'RT' => $this->RT,
            ]);
    }
}
