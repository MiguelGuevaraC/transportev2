<!DOCTYPE html>
<html>

<head>
    <title>Token de Verificación - Permiso de Administrador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
        }

        .container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 600px;
            margin: 40px auto;
        }

        .token {
            font-size: 24px;
            font-weight: bold;
            color: #007BFF;
            margin: 20px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>¡Hola, Administrador!</h2>
        <p>Se ha solicitado un token para verificar y otorgar permisos de administrador.</p>
        <p>Tu token de verificación es:</p>
        <p class="token">{{ $token }}</p>

        <p>Número de venta: {{ $moviment_number }}</p>

        @if (!empty($receptions))
            <p>Recepciones:</p>
            <ul>
                @foreach ($receptions as $reception)
                    <li>Código: {{ $reception['codeReception'] }} - GRT: {{ $reception['guideNumber'] }}</li>
                @endforeach
            </ul>
        @else
            <p>No hay recepciones disponibles.</p>
        @endif


        <p>Gracias,</p>
        <p>El equipo de soporte</p>
        <div class="footer">
            Este es un mensaje automático. No respondas a este correo. <br />
            Si necesitas ayuda, contacta con soporte técnico.
        </div>
    </div>
</body>

</html>
