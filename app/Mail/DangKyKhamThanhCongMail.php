<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DangKyKhamThanhCongMail extends Mailable
{
    use Queueable, SerializesModels;
    public $responeMos;

    public function __construct($responeMos)
    {
        $this->responeMos = $responeMos;
    }
    public function build()
    {
        return $this->subject('BVXA - Đăng ký khám thành công')
            ->view('emails.dang_ky_kham_thanh_cong')
            ->with([
                'responeMos' => $this->responeMos,
            ]);
    }
}
