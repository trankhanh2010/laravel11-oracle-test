<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP</title>
</head>
<body>
    <p>Mã OTP của bạn là: <strong>{{ $otp }}</strong></p>
    <p>Mã này có hiệu lực trong {{$otpTTL}} phút.</p>
    <p>Vui lòng không chia sẻ mã này với bất kỳ ai.</p>
</body>
</html>
