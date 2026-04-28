<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Sedang Dalam Perbaikan – {{ parse_url(config('app.url'), PHP_URL_HOST) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0ea5e9;
            --accent: #f59e0b;
            --text: #1e293b;
            --text-muted: #64748b;
            --bg: #f8fafc;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 60px 40px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.05);
        }

        .icon-wrapper {
            width: 100px;
            height: 100px;
            background: #f0f9ff;
            color: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 30px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--text);
        }

        p {
            font-size: 1.1rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 40px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary);
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(14, 165, 233, 0.2);
        }

        .socials {
            margin-top: 50px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .socials a {
            color: var(--text-muted);
            font-size: 1.5rem;
            transition: color 0.3s;
        }

        .socials a:hover {
            color: var(--primary);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-wrapper">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>
        <h1>Maintenance</h1>
        <p>{{ get_setting('maintenance_message') }}</p>
        
        <a href="https://wa.me/{{ config('services.whatsapp.bot_number') }}" class="btn">
            <i class="fa-brands fa-whatsapp"></i> Hubungi Admin
        </a>

        <div class="socials">
            <a href="{{ config('services.social.facebook') }}"><i class="fa-brands fa-facebook"></i></a>
            <a href="{{ config('services.social.instagram') }}"><i class="fa-brands fa-instagram"></i></a>
            <a href="{{ config('services.social.tiktok') }}"><i class="fa-brands fa-tiktok"></i></a>
            <a href="{{ config('services.social.youtube') }}"><i class="fa-brands fa-youtube"></i></a>
        </div>

        <div style="margin-top: 40px; font-size: 0.8rem; color: #cbd5e1;">
            &copy; {{ date('Y') }} {{ parse_url(config('app.url'), PHP_URL_HOST) }}
        </div>
    </div>
</body>
</html>
