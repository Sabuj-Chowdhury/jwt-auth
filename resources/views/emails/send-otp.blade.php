<!DOCTYPE html>
<html>
<head>
    <title>OTP for Password Reset</title>
</head>
<body>
    <h2>Hello {{ $userName }},</h2>
    <p>Your OTP for password reset is: <strong>{{ $otp }}</strong></p>
    <p>This OTP will expire in 10 minutes.</p>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>
