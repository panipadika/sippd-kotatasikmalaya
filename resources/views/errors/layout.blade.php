<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Data Sektoral Kota Tasikmalaya</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}?v=2">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-hover: #172554;
            --accent: #3b82f6;
            --bg-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-gradient);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            border-radius: 24px;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.1), 0 8px 16px -6px rgba(15, 23, 42, 0.05);
            max-width: 560px;
            width: 100%;
            padding: 44px 36px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #2563eb, #38bdf8, #2563eb);
        }

        .error-code {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 5.5rem;
            line-height: 1;
            letter-spacing: -2px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .app-logo {
            width: 68px;
            height: auto;
            margin-bottom: 20px;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.08));
        }

        .error-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .error-desc {
            font-size: 0.98rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 12px 26px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(30, 58, 138, 0.25);
        }

        .btn-action:hover {
            background: var(--primary-hover);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(30, 58, 138, 0.35);
        }

        .btn-secondary-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            color: #475569;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 12px 22px;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-secondary-action:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .footer-note {
            margin-top: 32px;
            font-size: 0.8rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <img src="{{ asset('logo.png') }}?v=2" alt="Logo Kota Tasikmalaya" class="app-logo">
        <div class="error-code">@yield('code')</div>
        <h1 class="error-title">@yield('heading')</h1>
        <p class="error-desc">@yield('message')</p>
        
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="{{ url('/') }}" class="btn-action">
                <i class="bi bi-speedometer2"></i> Kembali ke Dashboard
            </a>
            <button onclick="window.history.back()" class="btn-secondary-action">
                <i class="bi bi-arrow-left"></i> Halaman Sebelumnya
            </button>
        </div>

        <div class="footer-note">
            Sistem Informasi Profil Pembangunan Daerah (SIPPD)<br>
            Bappelitbangda Pemerintah Kota Tasikmalaya
        </div>
    </div>
</body>
</html>
