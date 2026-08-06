<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Kontak Layanan Baru</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border: 1px solid #e2e8f0;
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
        }
        .greeting {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .text {
            font-size: 15px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 24px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
            background: #f8fafc;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .details-table td {
            padding: 14px 18px;
            font-size: 14px;
            border-bottom: 1px solid #e2e8f0;
        }
        .details-table td.label {
            font-weight: 600;
            color: #475569;
            width: 35%;
        }
        .details-table td.value {
            color: #0f172a;
            font-weight: 700;
        }
        .details-table tr:last-child td {
            border-bottom: none;
        }
        .message-box {
            background-color: #fafaf9;
            border-left: 4px solid #eab308;
            padding: 20px;
            border-radius: 4px 12px 12px 4px;
            font-size: 15px;
            color: #1c1917;
            line-height: 1.6;
            white-space: pre-wrap;
            margin-bottom: 28px;
            font-style: italic;
        }
        .footer {
            background: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Lost Talent Detector</h1>
        </div>
        <div class="content">
            <div class="greeting">Halo, Administrator!</div>
            <div class="text">
                Ada pesan baru yang dikirimkan oleh pengunjung melalui form Kontak Layanan di Landing Page:
            </div>
            
            <table class="details-table">
                <tr>
                    <td class="label">Nama Pengirim</td>
                    <td class="value">{{ $name }}</td>
                </tr>
                <tr>
                    <td class="label">Alamat Email</td>
                    <td class="value">{{ $email }}</td>
                </tr>
                <tr>
                    <td class="label">Subjek / Topik</td>
                    <td class="value">{{ $msgSubject }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Kirim</td>
                    <td class="value">{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }} WIB</td>
                </tr>
            </table>

            <div class="greeting" style="font-size: 16px;">Isi Pesan:</div>
            <div class="message-box">
{{ $messageText }}
            </div>

            <div class="text" style="font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px;">
                Catatan: Anda dapat membalas email ini secara langsung untuk berkomunikasi dengan pengirim pesan.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Lost Talent Detector. All rights reserved.
        </div>
    </div>
</body>
</html>
