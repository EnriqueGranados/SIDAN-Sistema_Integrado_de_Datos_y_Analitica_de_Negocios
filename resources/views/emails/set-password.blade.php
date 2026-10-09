<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Restablece tu contraseña de SIDAN</title>
</head>

<body style="margin:0; padding:0; background-color:#f6f8fb; font-family:Arial, Helvetica, sans-serif; color:#334155;">

    <table
        role="presentation"
        width="100%"
        cellspacing="0"
        cellpadding="0"
        border="0"
        style="width:100%; background-color:#f6f8fb;">

        <tr>
            <td align="center" style="padding:40px 16px;">

                <table
                    role="presentation"
                    width="100%"
                    cellspacing="0"
                    cellpadding="0"
                    border="0"
                    style="width:100%; max-width:600px;">

                    {{-- Marca --}}
                    <tr>
                        <td align="center" style="padding-bottom:24px;">

                            <table
                                role="presentation"
                                cellspacing="0"
                                cellpadding="0"
                                border="0">

                                <tr>
                                    <td
                                        align="center"
                                        valign="middle"
                                        style="
                                            width:42px;
                                            height:42px;
                                            border-radius:12px;
                                            background-color:#0f172a;
                                            color:#ffffff;
                                            font-size:16px;
                                            font-weight:800;
                                        ">
                                        S
                                    </td>

                                    <td
                                        style="
                                            padding-left:10px;
                                            font-size:22px;
                                            font-weight:800;
                                            color:#0f172a;
                                        ">
                                        SIDAN
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Tarjeta --}}
                    <tr>
                        <td
                            style="
                                background-color:#ffffff;
                                border:1px solid #e2e8f0;
                                border-radius:24px;
                                padding:40px;
                            ">

                            <p
                                style="
                                    margin:0 0 12px;
                                    color:#22c55e;
                                    font-size:12px;
                                    font-weight:800;
                                    letter-spacing:2px;
                                    text-transform:uppercase;
                                ">
                                Recuperación de acceso
                            </p>

                            <h1
                                style="
                                    margin:0;
                                    color:#0f172a;
                                    font-size:28px;
                                    line-height:36px;
                                    font-weight:800;
                                ">
                                Restablece tu contraseña
                            </h1>

                            <p
                                style="
                                    margin:24px 0 0;
                                    color:#475569;
                                    font-size:15px;
                                    line-height:24px;
                                ">
                                Hola{{ $nombre ? ', ' . $nombre : '' }}.
                            </p>

                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Actualmente utilizas tu cuenta de Google para iniciar sesión en SIDAN
                                y todavía no tienes una contraseña propia configurada.
                            </p>

                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Puedes establecer una contraseña para iniciar sesión también
                                con tu correo electrónico y contraseña, sin perder el acceso
                                mediante Google.
                            </p>

                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Haz clic en el siguiente botón para configurar tu contraseña de SIDAN.
                            </p>

                            {{-- Botón --}}
                            <table
                                role="presentation"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                width="100%"
                                style="margin-top:28px;">

                                <tr>
                                    <td align="center">

                                        <a
                                            href="{{ $url }}"
                                            style="
                                                display:inline-block;
                                                background-color:#22c55e;
                                                color:#ffffff;
                                                text-decoration:none;
                                                font-size:14px;
                                                font-weight:800;
                                                padding:14px 28px;
                                                border-radius:12px;
                                            ">
                                            Establecer contraseña
                                        </a>

                                    </td>
                                </tr>
                            </table>

                            {{-- Expiración --}}
                            <table
                                role="presentation"
                                cellspacing="0"
                                cellpadding="0"
                                border="0"
                                width="100%"
                                style="
                                    margin-top:28px;
                                    background-color:#f8fafc;
                                    border-radius:12px;
                                ">

                                <tr>
                                    <td style="padding:16px;">

                                        <p
                                            style="
                                                margin:0;
                                                color:#64748b;
                                                font-size:13px;
                                                line-height:20px;
                                            ">
                                            Por seguridad, este enlace expirará en
                                            <strong style="color:#334155;">
                                                {{ $minutos }} minutos
                                            </strong>.
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; color:#64748b; font-size:13px; line-height:21px;">
                                Si no solicitaste establecer una contraseña, puedes ignorar
                                este mensaje. Tu acceso mediante Google seguirá funcionando.
                            </p>

                            <div
                                style="
                                    margin-top:28px;
                                    padding-top:24px;
                                    border-top:1px solid #e2e8f0;
                                ">

                                <p
                                    style="
                                        margin:0;
                                        color:#94a3b8;
                                        font-size:12px;
                                        line-height:19px;
                                    ">
                                    Si el botón no funciona, copia y pega este enlace
                                    en tu navegador:
                                </p>

                                <p
                                    style="
                                        margin:8px 0 0;
                                        font-size:12px;
                                        line-height:19px;
                                        word-break:break-all;
                                    ">
                                    <a
                                        href="{{ $url }}"
                                        style="color:#16a34a; text-decoration:none;">
                                        {{ $url }}
                                    </a>
                                </p>

                            </div>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            align="center"
                            style="
                                padding:24px 16px 0;
                                color:#94a3b8;
                                font-size:12px;
                                line-height:19px;
                            ">

                            <p style="margin:0;">
                                © {{ date('Y') }} SIDAN. Todos los derechos reservados.
                            </p>

                            <p style="margin:6px 0 0;">
                                Sistema Integrado de Datos y Analítica de Negocios
                            </p>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>
</html>