@php
    $accessToken = $AT;
    $refreshToken = $RT;
    $appName = config('app')['name'];
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }}: Cập nhật mới Access Token, Refresh Token Zalo</title>
</head>
<body>
    <p>AccessToken: {{ $accessToken }}</p>
    <p>RefreshToken: {{ $refreshToken }}</p>
</body>
</html>
