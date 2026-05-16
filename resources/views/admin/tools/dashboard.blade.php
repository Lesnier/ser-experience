<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consola de Gestión Masiva - SerExperience</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #0f172a;
            --text-light: #64748b;
            --primary: #4f46e5;
            --primary-light: #e0e7ff;
            --success: #10b981;
            --border: #e2e8f0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            padding: 40px 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
        }

        .header {
            margin-bottom: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
        }

        .header h1 {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(to right, #4f46e5, #9333ea);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-subtitle {
            font-size: 14px;
            color: var(--text-light);
            margin-top: 5px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-3px);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .card h2 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 25px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #334155;
        }

        select, input[type="text"], input[type="number"], input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            font-size: 14px;
            background-color: #f8fafc;
        }

        select:focus, input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .btn {
            width: 100%;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s;
        }

        .btn:hover { background: #4338ca; }

        /* Alert Banners */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 14px;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Tabla de credenciales generadas */
        .credentials-section {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .credentials-section h3 {
            font-size: 18px;
            margin-bottom: 20px;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        th {
            background: #f8fafc;
            color: var(--text-light);
            font-weight: 600;
        }

        .pass-badge {
            font-family: monospace;
            background: #e0e7ff;
            color: #3730a3;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div>
                <h1>Consola de Carga Masiva y Gestión Batch</h1>
                <p class="header-subtitle">Herramientas avanzadas para optimizar el despliegue logístico del organizador.</p>
            </div>
            <a href="{{ url('/admin') }}" style="font-size: 14px; text-decoration:none; color: var(--primary); font-weight: 600;">← Volver al Panel Principal</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid">
            <!-- TARJETA 1: IMPORTADOR DE MARCAS -->
            <div class="card">
                <div class="card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <h2>1. Importar Marcas / Stands (CSV)</h2>
                <p>Carga un listado de marcas. El sistema generará el stand, creará las cuentas de usuario asociadas y vinculará los accesos de forma automática en 1-Clic.</p>
                
                <form action="{{ route('admin.tools.import-brands') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Selecciona el Evento</label>
                        <select name="event_id" required>
                            @foreach($events as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Archivo CSV de Marcas</label>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <small style="color: var(--text-light); font-size: 11px; margin-top:5px; display:block;">Formato esperado: Marca,StandNum,EmailContacto</small>
                    </div>
                    <button type="submit" class="btn">Ejecutar Importación</button>
                </form>
            </div>

            <!-- TARJETA 2: GENERADOR BATCH DE CUPONES -->
            <div class="card" style="grid-row: span 2;">
                <div class="card-icon" style="background: #fae8ff; color: #a855f7;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                </div>
                <h2>2. Generador Batch de Cupones Base</h2>
                <p>Define un beneficio modelo aquí y el sistema creará instantáneamente una réplica para **TODAS** las marcas participantes en el evento.</p>
                
                <form action="{{ route('admin.tools.batch-coupons') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label>Evento Destino</label>
                        <select name="event_id" required>
                            @foreach($events as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Título del Cupón</label>
                        <input type="text" name="title" placeholder="Ej: 15% Dcto Especial Feria" required>
                    </div>
                    <div class="form-group">
                        <label>Tipo de Descuento</label>
                        <select name="discount_type" required>
                            <option value="percentage">Porcentaje (%)</option>
                            <option value="fixed_amount">Monto Fijo ($)</option>
                            <option value="freebie">Regalo / Servicio Gratis</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Valor numérico del Descuento</label>
                        <input type="number" step="0.01" name="discount_value" placeholder="Ej: 15.00" required>
                    </div>
                    <div class="form-group">
                        <label>Límite de usos por asistente</label>
                        <input type="number" name="usage_limit" value="1" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>Alcance de Validez</label>
                        <select name="validity_scope" required>
                            <option value="during_event">Solo durante el evento</option>
                            <option value="post_event">Post-evento (en locales comerciales)</option>
                            <option value="both">Ambos momentos</option>
                        </select>
                    </div>
                    <div class="form-group" style="display: flex; gap: 10px; align-items: center;">
                        <input type="checkbox" name="allow_brand_modification" id="allow_mod" value="1" style="width: auto;">
                        <label for="allow_mod" style="margin-bottom:0; cursor:pointer;">¿Permitir que la marca edite sus límites?</label>
                    </div>
                    <button type="submit" class="btn" style="background: #9333ea;">Crear Cupones en Bloque</button>
                </form>
            </div>

            <!-- TARJETA 3: IMPORTADOR DE ASISTENTES -->
            <div class="card">
                <div class="card-icon" style="background: #d1fae5; color: #059669;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
                <h2>3. Importar Asistentes / Invitados (CSV)</h2>
                <p>Sube bases de datos masivas de invitados. El sistema los registrará y emitirá instantáneamente su pase único (`entry_code` y `loyalty_code`).</p>
                
                <form action="{{ route('admin.tools.import-attendees') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Evento Destino</label>
                        <select name="event_id" required>
                            @foreach($events as $e)
                                <option value="{{ $e->id }}">{{ $e->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Archivo CSV de Asistentes</label>
                        <input type="file" name="csv_file" accept=".csv" required>
                        <small style="color: var(--text-light); font-size: 11px; margin-top:5px; display:block;">Formato: Nombre,Apellido,Correo,Telefono</small>
                    </div>
                    <button type="submit" class="btn" style="background: #059669;">Ejecutar Carga Asistentes</button>
                </form>
            </div>
        </div>

        <!-- SECCIÓN DE CREDENCIALES GENERADAS EN LA SESIÓN ACTUAL -->
        @if(session('temp_credentials'))
            <div class="credentials-section">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Credenciales Generadas Recientemente
                </h3>
                <p style="color: var(--text-light); font-size: 14px; margin-bottom: 20px;">⚠️ Copia estas credenciales ahora. Por motivos de seguridad, las contraseñas planas desaparecerán al cerrar tu navegador.</p>
                <table>
                    <thead>
                        <tr>
                            <th>Marca / Stand</th>
                            <th>Email de Acceso (Usuario)</th>
                            <th>Contraseña Temporal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(session('temp_credentials') as $cred)
                            <tr>
                                <td><strong>{{ $cred['brand'] }}</strong></td>
                                <td>{{ $cred['email'] }}</td>
                                <td><span class="pass-badge">{{ $cred['temp_pass'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</body>
</html>
