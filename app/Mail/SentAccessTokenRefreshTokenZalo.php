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
        dump([
                'AT' => $this->AT,
                'RT' => $this->RT,
        ]);
        return $this->subject('Mã AccessToken RefreshToken')
            ->view('emails.sent_AT_RT')
            ->with([
                'AT' => $this->AT,
                'RT' => $this->RT,
            ]);
    }
}
