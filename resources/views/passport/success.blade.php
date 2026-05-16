<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Registro Exitoso! - SerExperience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #064e3b 0%, #0f172a 100%);
            --accent-color: #10b981;
            --text-main: #f8fafc;
            --glass-bg: rgba(255, 255, 255, 0.04);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 450px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            animation: scaleIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .success-badge {
            width: 80px;
            height: 80px;
            background: rgba(16, 185, 129, 0.15);
            border: 2px solid var(--accent-color);
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.3);
        }

        .success-badge svg {
            width: 40px;
            height: 40px;
            color: var(--accent-color);
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 10px;
            color: #fff;
        }

        p {
            color: #94a3b8;
            font-size: 15px;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .qr-card {
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            display: inline-block;
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            margin-bottom: 25px;
        }

        .qr-card img {
            width: 200px;
            height: 200px;
            display: block;
        }

        .code-display {
            background: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            padding: 12px;
            font-family: monospace;
            font-size: 20px;
            letter-spacing: 2px;
            font-weight: bold;
            color: #a7f3d0;
            margin-bottom: 25px;
            border: 1px dashed rgba(16, 185, 129, 0.4);
        }

        .footer-note {
            font-size: 13px;
            color: #64748b;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding-top: 20px;
        }

        .footer-note strong {
            color: #cbd5e1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-badge">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>

        <h1>¡Registro Exitoso!</h1>
        <p>Hola <strong>{{ session('attendee_name') }}</strong>, tu Pasaporte de Beneficios para <strong>{{ session('event_name') }}</strong> ya está activo.</p>

        <div class="qr-card">
            <!-- Utilización de la API QR Server de forma dinámica para generar el código QR del Loyalty Code -->
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ session('loyalty_code') }}&bgcolor=ffffff" alt="QR Clave Única">
        </div>

        <div class="code-display">
            {{ session('loyalty_code') }}
        </div>

        <div class="footer-note">
            Te hemos enviado un correo electrónico con este código y las instrucciones. Muéstralo en los stands de las marcas aliadas para canjear tus regalos y descuentos.
        </div>
    </div>
</body>
</html>
