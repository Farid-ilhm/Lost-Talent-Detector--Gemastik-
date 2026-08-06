<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi OTP</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F7F5F0;
            margin: 0;
            padding: 0;
            color: #1C1917;
        }
        .container {
            max-width: 560px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid #E2DDD5;
        }
        .header {
            background: linear-gradient(135deg, #1c1917 0%, #44403c 100%);
            padding: 32px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        .content {
            padding: 36px 32px;
            text-align: center;
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1c1917;
            margin-bottom: 12px;
        }
        .text {
            font-size: 15px;
            color: #78716C;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .otp-card {
            background: #F7F5F0;
            border: 2px dashed #EAB308;
            border-radius: 12px;
            padding: 20px;
            margin: 0 auto 28px auto;
            display: inline-block;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            color: #1c1917;
        }
        .warning {
            font-size: 13px;
            color: #78716C;
            border-top: 1px solid #E2DDD5;
            padding-top: 20px;
            margin-top: 10px;
        }
        .footer {
            background: #F7F5F0;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #78716C;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Lost Talent Detector</h1>
        </div>
        <div class="content">
            <div class="greeting">Halo, {{ $userName }}!</div>
            <div class="text">
                Terima kasih telah mendaftar di platform <strong>Lost Talent Detector</strong>. Silakan gunakan kode verifikasi OTP berikut untuk mengaktifkan akun Anda:
            </div>
            
            <div class="otp-card">
                <div class="otp-code">{{ $otpCode }}</div>
            </div>

            <div class="text" style="margin-bottom: 10px;">
                Kode ini berlaku selama <strong>10 menit</strong>.
            </div>

            <div class="warning">
                Jika Anda tidak merasa melakukan pendaftaran akun ini, harap abaikan email ini. Jangan berikan kode ini kepada siapapun.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Lost Talent Detector. All rights reserved.
        </div>
    </div>
</body>
</html>
