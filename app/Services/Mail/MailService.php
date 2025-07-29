<?php

namespace App\Services\Mail;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use App\Mail\DangKyKhamThanhCongMail;
use App\Mail\SendInvalidRefreshTokenTokenZaloNotification;
use App\Mail\SentAccessTokenRefreshTokenZalo;

class MailService
{
    public function sendToDanhSachEmailNhanThongBaoLoi($message){
        $listEmail = config('database')['connections']['thong_bao']['danh_sach_email_nhan_thong_bao_loi'];
        foreach($listEmail as $key => $item){
            Mail::to($item)->send($message);
        }
    }

    public function sendOtp($email, $otpCode)
    {
        $message = new OtpMail($otpCode);
        Mail::to($email)->send($message);
        return true;
    }
    public function sendThongBaoDangKyKhamThanhCong($email, $responeMos)
    {
        $message = new DangKyKhamThanhCongMail($responeMos);
        Mail::to($email)->send($message);
        return true;
    }
    // Gửi để lưu lại cặp Token Zalo mới nhất
    public function sendTokenZalo($AT, $RT)
    {
        $message = new SentAccessTokenRefreshTokenZalo($AT, $RT);
        $this->sendToDanhSachEmailNhanThongBaoLoi($message);
        return true;
    }
    // Gửi khi không thể tự động refresh lại AccessToken do RefreshToken không hợp lệ
    public function sendInvalidRefreshTokenTokenZaloNotification($AT, $RT)
    {
        $message = new SendInvalidRefreshTokenTokenZaloNotification($AT, $RT);
        $this->sendToDanhSachEmailNhanThongBaoLoi($message);
        return true;
    }
}
