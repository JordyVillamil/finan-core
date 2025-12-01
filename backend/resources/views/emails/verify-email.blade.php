<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifica tu cuenta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .content h2 {
            color: #333;
            margin-top: 0;
        }
        .content p {
            color: #666;
            line-height: 1.6;
        }
        .token-box {
            background-color: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
        }
        .token {
            font-family: 'Courier New', monospace;
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            letter-spacing: 2px;
            word-break: break-all;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .button:hover {
            opacity: 0.9;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Finan-Core</h1>
            <p>Sistema Contable y Administrativo</p>
        </div>
        
        <div class="content">
            <h2>¡Hola, {{ $userName }}!</h2>
            
            <p>Gracias por registrarte en Finan-Core. Para completar tu registro y verificar tu cuenta, usa el siguiente token:</p>
            
            <div class="token-box">
                <p style="margin: 0 0 10px 0; color: #666;">Tu código de verificación:</p>
                <div class="token">{{ $verificationToken }}</div>
            </div>
            
            <p style="text-align: center;">O haz clic en el siguiente botón:</p>
            
            <p style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">✓ Verificar mi cuenta</a>
            </p>
            
            <div class="warning">
                <strong>⚠️ Importante:</strong> Este enlace expira en 24 horas. Si no solicitaste esta verificación, puedes ignorar este correo.
            </div>
            
            <p>Si tienes problemas con el botón, copia y pega este enlace en tu navegador:</p>
            <p style="word-break: break-all; color: #667eea;">{{ $verificationUrl }}</p>
        </div>
        
        <div class="footer">
            <p>© {{ date('Y') }} Finan-Core. Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>
</html>
