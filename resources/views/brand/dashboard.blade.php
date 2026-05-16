<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escáner de Stand - {{ $brand->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- HTML5-QRCode Library para escaneo en navegador sin apps extras -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <style>
        :root {
            --bg-main: #09090b;
            --card-bg: #18181b;
            --accent: #c084fc;
            --accent-rgb: 192, 132, 252;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --text: #f4f4f5;
            --text-muted: #71717a;
            --border: #27272a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-main);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Navbar */
        nav {
            background: var(--card-bg);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .brand-info h1 {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
        }

        .brand-info span {
            font-size: 12px;
            color: var(--accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .btn-logout {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: inherit;
        }

        /* Header Stats */
        .dashboard-header {
            padding: 20px;
            display: flex;
            gap: 15px;
        }

        .stat-card {
            flex: 1;
            background: var(--card-bg);
            border-radius: 16px;
            padding: 15px;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
        }

        .stat-card span {
            font-size: 12px;
            color: var(--text-muted);
            font-weight: 600;
            margin-bottom: 5px;
        }

        .stat-card strong {
            font-size: 24px;
            font-weight: 800;
            color: var(--accent);
        }

        /* Main Content & Scanner */
        main {
            flex: 1;
            padding: 0 20px 40px 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .scanner-section {
            background: var(--card-bg);
            border-radius: 24px;
            border: 1px solid var(--border);
            padding: 10px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }

        #reader {
            width: 100%;
            border: none !important;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
        }

        /* Sobreescribir estilos feos de html5-qrcode */
        #reader__dashboard_section_csr button {
            background: var(--accent) !important;
            color: #000 !important;
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 10px !important;
            font-family: 'Outfit', sans-serif !important;
            font-weight: 600 !important;
            font-size: 14px !important;
            margin-top: 10px !important;
            cursor: pointer;
        }
        #reader img { display: none; } /* ocultar imagen de loading */

        .manual-entry {
            background: var(--card-bg);
            border-radius: 20px;
            border: 1px solid var(--border);
            padding: 20px;
        }

        .manual-entry h2 {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #e4e4e7;
        }

        .input-group {
            display: flex;
            gap: 10px;
        }

        .input-group input {
            flex: 1;
            background: var(--bg-main);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            color: #fff;
            font-family: monospace;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .input-group input:focus {
            outline: none;
            border-color: var(--accent);
        }

        .btn-primary {
            background: var(--accent);
            color: #000;
            border: none;
            border-radius: 12px;
            padding: 0 20px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.2s;
            font-family: inherit;
        }

        .btn-primary:active { opacity: 0.8; }

        /* Modal Estilo Lámina (Sheet Modal de iOS) para Fricción Cero */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(4px);
            z-index: 100;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            display: flex;
            align-items: flex-end;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-sheet {
            width: 100%;
            background: #1c1917;
            border-radius: 24px 24px 0 0;
            padding: 30px 24px 40px 24px;
            box-shadow: 0 -10px 40px rgba(0,0,0,0.5);
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            max-height: 90vh;
            overflow-y: auto;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .modal-overlay.active .modal-sheet {
            transform: translateY(0);
        }

        .modal-handle {
            width: 40px;
            height: 4px;
            background: rgba(255,255,255,0.15);
            border-radius: 2px;
            margin: -15px auto 20px auto;
        }

        .attendee-info-card {
            text-align: center;
            margin-bottom: 25px;
        }

        .attendee-info-card h3 {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .attendee-info-card p {
            font-size: 14px;
            color: var(--text-muted);
        }

        .coupon-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .coupon-item {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .coupon-item.disabled {
            opacity: 0.5;
            border-color: transparent;
            background: rgba(255,255,255,0.01);
        }

        .coupon-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .coupon-title {
            font-weight: 700;
            font-size: 16px;
            color: #fff;
        }

        .coupon-badge {
            font-size: 11px;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .badge-available {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
        }

        .badge-exhausted {
            background: rgba(239, 68, 68, 0.15);
            color: var(--error);
        }

        .coupon-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 15px;
        }

        .btn-redeem-giant {
            width: 100%;
            background: var(--success);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 16px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.2s, filter 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-redeem-giant:active {
            transform: scale(0.98);
            filter: brightness(0.9);
        }

        .btn-close-modal {
            width: 100%;
            background: transparent;
            border: 1px solid rgba(255,255,255,0.1);
            color: var(--text-muted);
            border-radius: 12px;
            padding: 14px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 15px;
            cursor: pointer;
        }

        /* Notificaciones emergentes (Toasts) */
        .toast {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            background: #fff;
            color: #000;
            padding: 15px;
            border-radius: 12px;
            font-weight: 700;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            z-index: 200;
            transform: translateY(150%);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .toast.active { transform: translateY(0); }
        .toast.toast-success { background: var(--success); color: #fff; }
        .toast.toast-error { background: var(--error); color: #fff; }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav>
        <div class="brand-info">
            <h1>{{ $brand->name }}</h1>
            <span>Stand {{ $brand->stand_number ?? 'S/N' }}</span>
        </div>
        <form action="{{ route('brand.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn-logout">Cerrar Sesión</button>
        </form>
    </nav>

    <!-- Quick Stats -->
    <div class="dashboard-header">
        <div class="stat-card">
            <span>Redenciones de Hoy</span>
            <strong id="today-count">{{ $todayRedemptions }}</strong>
        </div>
        <div class="stat-card">
            <span>Estado Stand</span>
            <strong style="color: var(--success);">Activo</strong>
        </div>
    </div>

    <!-- Main App Space -->
    <main>
        <!-- Lente del Escáner QR -->
        <div class="scanner-section">
            <div id="reader"></div>
        </div>

        <!-- Respaldo: Ingreso Manual -->
        <div class="manual-entry">
            <h2>¿Pantalla dañada? Ingresa el código manual:</h2>
            <div class="input-group">
                <input type="text" id="manual-code-input" placeholder="LOY-XXXXXXXX" autocomplete="off">
                <button type="button" class="btn-primary" id="btn-manual-check">Verificar</button>
            </div>
        </div>
    </main>

    <!-- Modal Deslizante (Sheet) de Resultados -->
    <div class="modal-overlay" id="result-modal">
        <div class="modal-sheet">
            <div class="modal-handle"></div>
            
            <div class="attendee-info-card">
                <p id="modal-event-tag" style="color: var(--accent); font-weight: 700; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Asistente Confirmado</p>
                <h3 id="modal-attendee-name">---</h3>
                <p id="modal-attendee-email">---</p>
            </div>

            <div class="coupon-list" id="modal-coupons-wrapper">
                <!-- Dinámicamente poblado por JS -->
            </div>

            <button type="button" class="btn-close-modal" id="btn-close-modal">Volver al Escáner</button>
        </div>
    </div>

    <!-- Toasts Globales -->
    <div class="toast" id="global-toast">¡Operación exitosa!</div>

    <!-- Scripts de Control e Interacción -->
    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const modal = document.getElementById('result-modal');
        let lastScanTime = 0;
        let html5QrcodeScanner = null;

        // Configuración del Escáner HTML5
        function startScanner() {
            html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: { width: 250, height: 250 },
                    rememberLastUsedCamera: true,
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
                },
                /* verbose= */ false
            );
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Throttle de 3 segundos entre escaneos para evitar lecturas múltiples rápidas
            const now = Date.now();
            if (now - lastScanTime < 3000) return;
            lastScanTime = now;

            // Alerta sutil con vibración nativa si el navegador lo soporta
            if (navigator.vibrate) navigator.vibrate(100);

            verifyAttendeeCode(decodedText);
        }

        function onScanFailure(error) {
            // Captura fallas silenciosas mientras busca QR, no hacemos nada para no inundar logs
        }

        // Petición de Verificación al Servidor
        async function verifyAttendeeCode(code) {
            showLoading();
            try {
                const response = await fetch("{{ route('brand.verify') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ loyalty_code: code })
                });
                
                const data = await response.json();
                
                if (response.ok && data.success) {
                    populateAndShowModal(data);
                } else {
                    showToast(data.message || 'Código inválido o inactivo', 'error');
                }
            } catch (error) {
                showToast('Error de conexión con el servidor', 'error');
            } finally {
                hideLoading();
            }
        }

        // Carga dinámica del modal con cupones disponibles
        function populateAndShowModal(data) {
            document.getElementById('modal-attendee-name').textContent = data.attendee.name;
            document.getElementById('modal-attendee-email').textContent = data.attendee.email + (data.attendee.company ? ` • ${data.attendee.company}` : '');
            
            const wrapper = document.getElementById('modal-coupons-wrapper');
            wrapper.innerHTML = '';

            data.coupons.forEach(coupon => {
                const item = document.createElement('div');
                item.className = `coupon-item ${coupon.is_available ? '' : 'disabled'}`;
                
                const badgeClass = coupon.is_available ? 'badge-available' : 'badge-exhausted';
                const badgeText = coupon.is_available ? 'Disponible' : 'Consumido';

                // Mostrar el valor del descuento de forma amigable
                let valStr = coupon.discount_type === 'percentage' ? `${parseFloat(coupon.discount_value)}% Dcto.` : 
                             coupon.discount_type === 'freebie' ? 'Gratis/Regalo' : `$${parseFloat(coupon.discount_value)} Dcto.`;

                let content = `
                    <div class="coupon-header">
                        <span class="coupon-title">${coupon.title} (${valStr})</span>
                        <span class="coupon-badge ${badgeClass}">${badgeText}</span>
                    </div>
                    <div class="coupon-desc">
                        ${coupon.description || 'Sin descripción adicional.'}<br>
                        <small style="color: #a1a1aa; margin-top:5px; display:inline-block;">Uso actual: ${coupon.current_uses} de ${coupon.usage_limit} permitidos.</small>
                    </div>
                `;

                if (coupon.is_available) {
                    content += `
                        <button type="button" class="btn-redeem-giant" onclick="redeem(${data.registration_id}, ${coupon.id})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            REGISTRAR CANJE (1-TAP)
                        </button>
                    `;
                } else {
                    content += `
                        <div style="text-align:center; color: var(--error); font-weight:700; font-size:13px;">
                            ${coupon.status_message}
                        </div>
                    `;
                }

                item.innerHTML = content;
                wrapper.appendChild(item);
            });

            modal.classList.add('active');
        }

        // Acción de Canje Definitiva (Llamada a la API)
        async function redeem(registrationId, couponId) {
            // Deshabilitar el botón para prevenir clicks dobles en UI
            const btn = event.currentTarget;
            const originalHTML = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = 'Procesando...';

            try {
                const response = await fetch("{{ route('brand.redeem') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        registration_id: registrationId,
                        loyalty_coupon_id: couponId,
                        notes: 'Redimido en Stand vía Escáner Móvil'
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Éxito total
                    showToast('¡Canje guardado con éxito!', 'success');
                    
                    // Actualizar el contador visual en el dashboard de fondo
                    const countEl = document.getElementById('today-count');
                    countEl.textContent = parseInt(countEl.textContent) + 1;

                    // Cerrar el modal y limpiar la pantalla rápidamente para el siguiente escaneo
                    closeModal();
                } else {
                    showToast(data.message || 'Ocurrió un error en el canje.', 'error');
                    btn.disabled = false;
                    btn.innerHTML = originalHTML;
                }
            } catch (e) {
                showToast('Error de red al registrar el canje.', 'error');
                btn.disabled = false;
                btn.innerHTML = originalHTML;
            }
        }

        // Utilidades visuales
        function closeModal() {
            modal.classList.remove('active');
            document.getElementById('manual-code-input').value = '';
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('global-toast');
            toast.textContent = message;
            toast.className = `toast toast-${type} active`;
            setTimeout(() => {
                toast.classList.remove('active');
            }, 4000);
        }

        function showLoading() {
            document.body.style.cursor = 'wait';
        }

        function hideLoading() {
            document.body.style.cursor = 'default';
        }

        // Event Listeners
        document.getElementById('btn-close-modal').addEventListener('click', closeModal);
        
        document.getElementById('btn-manual-check').addEventListener('click', () => {
            const code = document.getElementById('manual-code-input').value;
            if (code.trim().length > 0) {
                verifyAttendeeCode(code);
            } else {
                showToast('Escribe un código primero', 'error');
            }
        });

        // Lanzar el escáner al cargar la página
        document.addEventListener('DOMContentLoaded', startScanner);
    </script>
</body>
</html>
