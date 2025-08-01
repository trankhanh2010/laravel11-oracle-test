<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SentAccessTokenRefreshTokenZalo extends Mailable
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
        return $this->subject(config('app')['name'].': Cập nhật mới Access Token, Refresh Token Zalo')
            ->view('emails.sent_AT_RT')
            ->with([
                'AT' => $this->AT,
                'RT' => $this->RT,
            ]);
    }
}
