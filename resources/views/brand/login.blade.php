<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Portal de Marcas - SerExperience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #09090b 0%, #18181b 100%);
            --accent-color: #c084fc;
            --text-main: #f4f4f5;
            --glass-bg: rgba(255, 255, 255, 0.03);
            --glass-border: rgba(255, 255, 255, 0.08);
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
            max-width: 400px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 45px 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .badge {
            background: rgba(192, 132, 252, 0.1);
            color: var(--accent-color);
            padding: 6px 12px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            text-transform: uppercase;
            display: inline-block;
            margin-bottom: 15px;
            border: 1px solid rgba(192, 132, 252, 0.2);
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.5px;
        }

        .form-group {
            margin-bottom: 22px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #a1a1aa;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            background: #09090b;
            border: 1px solid #27272a;
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(192, 132, 252, 0.15);
        }

        button {
            width: 100%;
            background: #fff;
            color: #09090b;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background: #e4e4e7;
            transform: translateY(-1px);
        }

        .error-banner {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 25px;
            color: #fca5a5;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="badge">Portal de Marcas</div>
            <h1>Iniciar Sesión</h1>
        </div>

        @if ($errors->any())
            <div class="error-banner">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('brand.login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Correo del Stand / Usuario</label>
                <input type="email" name="email" id="email" placeholder="ejemplo@marca.com" value="{{ old('email') }}" required autocomplete="username">
            </div>

            <div class="form-group">
                <label for="password">Contraseña de Acceso</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password">
            </div>

            <button type="submit">
                Acceder al Escáner
            </button>
        </form>
    </div>
</body>
</html>
