<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Kata Sandi - SIPPD Kota Tasikmalaya</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 24px;
            color: #1e293b;
        }
        .container {
            max-width: 560px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #064e3b 0%, #0d9488 100%);
            padding: 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -0.01em;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        .body {
            padding: 32px 28px;
            line-height: 1.6;
            font-size: 14px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            color: #0f172a;
        }
        .btn-wrapper {
            text-align: center;
            margin: 28px 0;
        }
        .btn {
            display: inline-block;
            background: #0d9488;
            color: #ffffff !important;
            padding: 12px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            box-shadow: 0 4px 10px rgba(13, 148, 136, 0.35);
        }
        .notice {
            background: #f8fafc;
            border-left: 4px solid #0d9488;
            padding: 12px 16px;
            margin: 20px 0;
            font-size: 12.5px;
            color: #64748b;
            border-radius: 4px;
        }
        .link-alt {
            font-size: 12px;
            color: #94a3b8;
            word-break: break-all;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
        }
        .footer {
            background: #f8fafc;
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>SIPPD KOTA TASIKMALAYA</h1>
            <p>Sistem Informasi Data Profil Pembangunan Daerah</p>
        </div>

        <div class="body">
            <div class="greeting">Halo, {{ $user->name }}</div>
            <p>Kami menerima permohonan untuk mengatur ulang kata sandi akun Anda pada portal <strong>SIPPD Kota Tasikmalaya</strong>.</p>
            <p>Silakan klik tombol di bawah ini untuk melanjutkan pembuatan kata sandi baru:</p>

            <div class="btn-wrapper">
                <a href="{{ $resetUrl }}" class="btn" target="_blank">Atur Ulang Kata Sandi Saya</a>
            </div>

            <div class="notice">
                <strong>Catatan Keamanan:</strong>
                <br>• Tautan ini hanya berlaku selama <strong>60 menit</strong> sejak email ini dikirimkan.
                <br>• Jika Anda tidak pernah meminta reset kata sandi, abaikan email ini dan kata sandi Anda akan tetap aman.
            </div>

            <div class="link-alt">
                Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke peramban (browser) Anda:
                <br>
                <a href="{{ $resetUrl }}" style="color: #0d9488;">{{ $resetUrl }}</a>
            </div>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Bappelitbangda Kota Tasikmalaya. Hak Cipta Dilindungi.<br>
            Pemerintah Kota Tasikmalaya - Jawa Barat
        </div>
    </div>
</body>
</html>

