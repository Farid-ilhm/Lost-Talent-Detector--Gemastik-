<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Institusi Baru</title>
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
        .btn-container {
            text-align: center;
            margin-bottom: 28px;
        }
        .btn {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: #ffffff !important;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 14px rgba(79, 70, 229, 0.4);
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
                Pemberitahuan sistem: Ada institusi baru yang telah mendaftarkan akun di platform <strong>Lost Talent Detector</strong> dan saat ini statusnya sedang menunggu persetujuan/verifikasi dari Anda.
            </div>
            
            <table class="details-table">
                <tr>
                    <td class="label">Nama Institusi</td>
                    <td class="value">{{ $institutionUser->name }}</td>
                </tr>
                <tr>
                    <td class="label">NPSN</td>
                    <td class="value">{{ $npsn }}</td>
                </tr>
                <tr>
                    <td class="label">Email Pendaftar</td>
                    <td class="value">{{ $institutionUser->email }}</td>
                </tr>
                <tr>
                    <td class="label">No. Telepon</td>
                    <td class="value">{{ $institutionUser->phone ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="label">Waktu Daftar</td>
                    <td class="value">{{ \Carbon\Carbon::now()->isoFormat('D MMMM YYYY, HH:mm') }} WIB</td>
                </tr>
            </table>

            <div class="btn-container">
                <a href="{{ $dashboardUrl }}" class="btn" target="_blank">Buka Dashboard & Verifikasi</a>
            </div>

            <div class="text" style="font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 20px;">
                Catatan: Setelah Anda memverifikasi institusi ini, mereka akan dapat mendaftarkan guru pembimbing dan siswa mereka secara penuh ke platform.
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Lost Talent Detector. All rights reserved.
        </div>
    </div>
</body>
</html>
