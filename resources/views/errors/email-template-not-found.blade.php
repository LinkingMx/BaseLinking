<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template No Encontrado - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .error-title {
            color: #dc2626;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .error-message {
            color: #6b7280;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .template-key {
            background: #fee2e2;
            color: #dc2626;
            padding: 8px 12px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 14px;
            margin: 10px 0;
            display: inline-block;
        }

        .suggestions {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }

        .suggestions h3 {
            margin-top: 0;
            color: #374151;
        }

        .suggestions ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .suggestions li {
            margin: 8px 0;
            color: #6b7280;
        }

        .back-link {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: 500;
        }

        .back-link:hover {
            background: #2563eb;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-icon">📧❌</div>

        <h1 class="error-title">Template de Email No Encontrado</h1>

        <p class="error-message">
            El template con la clave
            <span class="template-key">{{ $templateKey }}</span>
            no pudo ser encontrado o no está activo.
        </p>

        @if (isset($error))
            <div
                style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 15px; margin: 20px 0;">
                <p style="margin: 0; color: #dc2626; font-size: 14px;">
                    <strong>Error:</strong> {{ $error }}
                </p>
            </div>
        @endif

        <div class="suggestions">
            <h3>💡 Sugerencias:</h3>
            <ul>
                <li>Verifica que el template exista en la base de datos</li>
                <li>Asegúrate de que el template esté marcado como activo</li>
                <li>Comprueba que la clave del template sea correcta</li>
                <li>Verifica el idioma del template (por defecto: 'es')</li>
            </ul>

            <h3>🔗 Templates de ejemplo disponibles:</h3>
            <ul>
                <li><code style="background: #e5e7eb; padding: 2px 6px; border-radius: 3px;">backup-success</code> -
                    Respaldo exitoso</li>
                <li><code style="background: #e5e7eb; padding: 2px 6px; border-radius: 3px;">user-welcome</code> -
                    Bienvenida de usuario</li>
                <li><code style="background: #e5e7eb; padding: 2px 6px; border-radius: 3px;">document-approved</code> -
                    Documento aprobado</li>
                <li><code style="background: #e5e7eb; padding: 2px 6px; border-radius: 3px;">bank-created</code> - Banco
                    creado</li>
            </ul>
        </div>

        <a href="{{ route('email.preview.wrapper') }}" class="back-link">
            ← Ver Template Base (Wrapper)
        </a>

        <div style="margin-top: 30px; color: #9ca3af; font-size: 14px;">
            <p>Para crear templates personalizados, accede al panel de administración.</p>
        </div>
    </div>
</body>

</html>
