<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro Pasaporte - SerExperience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            --accent-color: #6366f1;
            --accent-hover: #4f46e5;
            --text-main: #f8fafc;
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
            overflow-x: hidden;
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
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-text {
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
            background: linear-gradient(90deg, #c084fc, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 14px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 600;
        }

        .event-card {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid var(--accent-color);
        }

        .event-card h3 {
            font-size: 16px;
            color: #e2e8f0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
        }

        input {
            width: 100%;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
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
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2);
            background: rgba(0, 0, 0, 0.3);
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .error-list {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 20px;
            list-style-position: inside;
            color: #fca5a5;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo-text">SerExperience</div>
            <div class="subtitle">Pasaporte del Evento</div>
        </div>

        @if($event)
            <div class="event-card">
                <h3>Estás registrándote para:<br><strong style="color: #fff;">{{ $event->name }}</strong></h3>
            </div>
        @else
            <div class="event-card" style="border-left-color: #ef4444;">
                <h3 style="color: #fca5a5;">No hay un evento configurado activo</h3>
            </div>
        @endif

        @if ($errors->any())
            <ul class="error-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        <form action="{{ route('passport.register.post') }}" method="POST">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event ? $event->id : '' }}">

            <div class="form-group">
                <label for="first_name">Nombres</label>
                <input type="text" name="first_name" id="first_name" placeholder="Ej. Maria Laura" value="{{ old('first_name') }}" required>
            </div>

            <div class="form-group">
                <label for="last_name">Apellidos (Opcional)</label>
                <input type="text" name="last_name" id="last_name" placeholder="Ej. Pérez Rojas" value="{{ old('last_name') }}">
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" id="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label for="phone">Número de Teléfono / WhatsApp</label>
                <input type="tel" name="phone" id="phone" placeholder="Ej. +593 987654321" value="{{ old('phone') }}" required>
            </div>

            <button type="submit" @if(!$event) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                Obtener mi Clave de Beneficios
            </button>
        </form>
    </div>
</body>
</html>
