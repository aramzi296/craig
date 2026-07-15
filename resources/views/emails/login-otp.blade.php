<!DOCTYPE html>
<html>
<head>
    <title>Kode OTP Login</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #333;">Kode OTP Login Anda</h2>
        <p>Halo,</p>
        <p>Gunakan kode OTP berikut untuk masuk ke akun Anda. Kode ini berlaku selama 10 menit.</p>
        
        <div style="background-color: #f8f9fa; border-radius: 8px; padding: 15px; text-align: center; margin: 20px 0;">
            <h1 style="letter-spacing: 5px; color: #0056b3; margin: 0;">{{ $otp }}</h1>
        </div>
        
        <p>Jika Anda tidak meminta kode ini, abaikan email ini.</p>
        <p>Terima kasih,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>
