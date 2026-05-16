<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Tu Pasaporte de Beneficios - SerExperience</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f1f5f9;
            padding-bottom: 40px;
            padding-top: 40px;
        }
        .main-table {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .header {
            background: linear-gradient(135deg, #4f46e5 0%, #1e1b4b 100%);
            text-align: center;
            padding: 40px 20px;
        }
        .header h1 {
            color: #ffffff;
            font-size: 24px;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .content {
            padding: 40px 30px;
            text-align: center;
            color: #334155;
        }
        .content h2 {
            font-size: 20px;
            color: #0f172a;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .content p {
            font-size: 16px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 30px;
        }
        .qr-container {
            display: inline-block;
            background-color: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .code-box {
            font-family: Courier, monospace;
            font-size: 22px;
            font-weight: bold;
            color: #4f46e5;
            background-color: #e0e7ff;
            padding: 10px 20px;
            border-radius: 8px;
            display: inline-block;
            letter-spacing: 3px;
            margin-bottom: 30px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid #edf2f7;
        }
        .highlight {
            color: #4f46e5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <center class="wrapper">
        <table class="main-table" cellpadding="0" cellspacing="0" border="0">
            <!-- Header -->
            <tr>
                <td class="header">
                    <h1>{{ $event->name }}</h1>
                    <p style="color: #c7d2fe; font-size: 14px; margin-top: 5px; text-transform: uppercase; letter-spacing: 1px;">Pasaporte de Beneficios Activo</p>
                </td>
            </tr>
            <!-- Body -->
            <tr>
                <td class="content">
                    <h2>¡Hola {{ $attendee->first_name }}!</h2>
                    <p>Gracias por ser parte de nuestro evento. Tu cuenta se ha generado con éxito y tu **Pasaporte de Beneficios Digital** está activado.</p>
                    
                    <p style="margin-bottom: 15px;"><strong>Presenta este código QR en el Stand o Local de las marcas participantes:</strong></p>
                    
                    <div class="qr-container">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ $loyalty_code }}" alt="Tu Código QR" width="180" height="180">
                    </div>
                    
                    <br>
                    
                    <div class="code-box">
                        {{ $loyalty_code }}
                    </div>
                    
                    <p style="font-size: 14px; background-color: #f8fafc; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981; text-align: left;">
                        💡 <strong>¿Cómo funciona?</strong><br>
                        1. Acércate al stand durante la feria o visita el local comercial post-evento.<br>
                        2. Muestra este correo para que el personal escanee tu código.<br>
                        3. ¡Disfruta de los servicios gratuitos, regalos y descuentos aplicados en segundos!
                    </p>
                </td>
            </tr>
            <!-- Footer -->
            <tr>
                <td class="footer">
                    Este es un correo automático generado por la plataforma de eventos.<br>
                    &copy; {{ date('Y') }} SerExperience. Todos los derechos reservados.
                </td>
            </tr>
        </table>
    </center>
</body>
</html>
