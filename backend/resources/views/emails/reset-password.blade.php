<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 32px;
            font-weight: bold;
            color: #DC2626;
        }
        h1 {
            color: #DC2626;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background-color: #DC2626;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #B91C1C;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .warning {
            background-color: #FEF3C7;
            padding: 15px;
            border-left: 4px solid #F59E0B;
            margin: 20px 0;
            border-radius: 5px;
        }
        .security {
            background-color: #DBEAFE;
            padding: 15px;
            border-left: 4px solid #3B82F6;
            margin: 20px 0;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🔐 Finan Core</div>
        </div>

        <h1>¡Hola {{ $userName }}!</h1>

        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en <strong>Finan Core</strong>.</p>

        <p>Si fuiste tú quien hizo esta solicitud, haz clic en el botón de abajo para crear una nueva contraseña:</p>

        <div style="text-align: center;">
            <a href="{{ $resetUrl }}" class="button">
                🔑 Restablecer mi contraseña
            </a>
        </div>

        <div class="warning">
            <strong>⏰ Importante:</strong> Este enlace expirará en <strong>60 minutos</strong>.
        </div>

        <p>Si no puedes hacer clic en el botón, copia y pega esta URL en tu navegador:</p>
        <p style="word-break: break-all; color: #6b7280; font-size: 14px;">
            {{ $resetUrl }}
        </p>

        <div class="security">
            <strong>🛡️ Seguridad:</strong> Si <strong>NO</strong> solicitaste restablecer tu contraseña, ignora este email. 
            Tu cuenta está segura y tu contraseña no será cambiada.
        </div>

        <p><strong>Consejos de seguridad:</strong></p>
        <ul>
            <li>Nunca compartas tu contraseña con nadie</li>
            <li>Usa una contraseña única y fuerte</li>
            <li>Activa la verificación en dos pasos</li>
        </ul>

        <div class="footer">
            <p><strong>Finan Core</strong> - Sistema Contable Empresarial</p>
            <p>Este es un email automático, por favor no respondas a este mensaje.</p>
            <p>&copy; {{ date('Y') }} Finan Core. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>