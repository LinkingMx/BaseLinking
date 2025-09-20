<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name') }}</title>
    <style>
        /* Email-safe CSS styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
        }

        .email-wrapper {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 1rem;
        }
    </style>
</head>

<body>
    <div class="email-wrapper">
        <!-- Header with Logo -->
        <div style="">
            @php
                $appearanceSettings = app(\App\Settings\AppearanceSettings::class);
                $generalSettings = app(\App\Settings\GeneralSettings::class);
                $logoUrl = $appearanceSettings->logo_url ?? null;
                $appName = $generalSettings->app_name ?? config('app.name');
            @endphp

            <img src="http://baselinking.test/logos/01K1Q47KVZ36ENXASN8P1NA5YE.png" alt="logo app"
                style="width: 150px"><br>
            {{ $appName }}
        </div>

        <!-- Main Content -->
        <div>
            {!! $content !!}
        </div>

        <!-- Footer -->
        <div>

            <p style="margin-top: 20px;">
                © {{ date('Y') }} Costeno Inc, Todos los derechos reservados.
            </p>

            <p style="margin-top: 40px; font-size: 11px; color: #9ca3af; text-align: justify;">
                Aviso: Este es un mensaje generado de manera automática por nuestro sistema. Por favor, no responda a
                este correo, ya que la bandeja de entrada no es monitoreada y no recibirá una respuesta
            </p>
            <p style="margin-top: 10px; font-size: 11px; color: #9ca3af; text-align: justify;">
                AVISO DE CONFIDENCIALIDAD: Este mensaje, incluyendo sus archivos adjuntos, contiene información de
                carácter confidencial y/o privilegiado, y está dirigido exclusivamente a la persona o entidad
                destinataria. Si usted ha recibido este correo por error, le informamos que su lectura, copia, uso o
                distribución están prohibidos por la ley. En tal caso, le rogamos que nos lo notifique de inmediato
                respondiendo a este correo, proceda a su eliminación definitiva y se abstenga de revelar su contenido a
                terceros.
            </p>
        </div>
    </div>
</body>

</html>
