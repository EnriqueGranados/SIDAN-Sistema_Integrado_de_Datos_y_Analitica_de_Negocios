<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a SIDAN</title>
</head>

<body style="margin:0; padding:0; background-color:#f6f8fb; font-family:Arial, Helvetica, sans-serif; color:#334155;">

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="width:100%; background-color:#f6f8fb;">
        <tr>
            <td align="center" style="padding:40px 16px;">

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                       style="width:100%; max-width:600px;">

                    {{-- Marca --}}
                    <tr>
                        <td align="center" style="padding-bottom:24px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td align="center" valign="middle"
                                        style="width:42px; height:42px; border-radius:12px; background-color:#0f172a; color:#ffffff; font-size:16px; font-weight:800;">
                                        S
                                    </td>

                                    <td style="padding-left:10px; font-size:22px; font-weight:800; color:#0f172a;">
                                        SIDAN
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Tarjeta --}}
                    <tr>
                        <td style="background-color:#ffffff; border:1px solid #e2e8f0; border-radius:24px; padding:40px;">

                            <p style="margin:0 0 12px; color:#22c55e; font-size:12px; font-weight:800; letter-spacing:2px; text-transform:uppercase;">
                                Bienvenido a SIDAN
                            </p>

                            <h1 style="margin:0; color:#0f172a; font-size:28px; line-height:36px; font-weight:800;">
                                Tu cuenta ha sido creada
                            </h1>

                            <p style="margin:24px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Hola{{ $nombre ? ', ' . $nombre : '' }}.
                            </p>

                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Un administrador ha creado una cuenta para ti en el
                                <strong style="color:#0f172a;">Sistema Integrado de Datos y Analítica de Negocios (SIDAN)</strong>.
                            </p>

                            <p style="margin:12px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Ya puedes iniciar sesión utilizando las siguientes credenciales temporales:
                            </p>

                            {{-- Credenciales --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="width:100%; margin-top:28px; background-color:#f8fafc; border:1px solid #e2e8f0; border-radius:12px;">
                                <tr>
                                    <td style="padding:20px;">

                                        <p style="margin:0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">
                                            Correo electrónico
                                        </p>

                                        <p style="margin:8px 0 20px; color:#0f172a; font-size:15px; line-height:22px; font-weight:700; word-break:break-word;">
                                            {{ $correo }}
                                        </p>

                                        <p style="margin:0; color:#64748b; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px;">
                                            Contraseña temporal
                                        </p>

                                        <p style="margin:8px 0 0; color:#0f172a; font-size:17px; line-height:25px; font-weight:800; letter-spacing:1px; word-break:break-all;">
                                            {{ $passwordTemporal }}
                                        </p>

                                    </td>
                                </tr>
                            </table>

                            {{-- Aviso de seguridad --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="width:100%; margin-top:20px; background-color:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0; color:#166534; font-size:13px; line-height:21px;">
                                            <strong>Importante:</strong>
                                            Por seguridad, al iniciar sesión por primera vez
                                            deberás cambiar esta contraseña temporal por una
                                            contraseña personal antes de acceder al sistema.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; color:#475569; font-size:15px; line-height:24px;">
                                Haz clic en el siguiente botón para ingresar a SIDAN.
                            </p>

                            {{-- Botón --}}
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                                   style="margin-top:28px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $url }}"
                                           style="display:inline-block; background-color:#22c55e; color:#ffffff; text-decoration:none; font-size:14px; font-weight:800; padding:14px 28px; border-radius:12px;">
                                            Iniciar sesión en SIDAN
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; color:#64748b; font-size:13px; line-height:21px;">
                                Te recomendamos no compartir estas credenciales con otras personas.
                                Una vez que establezcas tu nueva contraseña, utiliza únicamente
                                esa contraseña para tus próximos accesos.
                            </p>

                            <p style="margin:12px 0 0; color:#64748b; font-size:13px; line-height:21px;">
                                Si no reconoces esta cuenta, comunícate con el administrador del sistema.
                            </p>

                            {{-- Enlace alternativo --}}
                            <div style="margin-top:28px; padding-top:24px; border-top:1px solid #e2e8f0;">

                                <p style="margin:0; color:#94a3b8; font-size:12px; line-height:19px;">
                                    Si el botón no funciona, copia y pega este enlace en tu navegador:
                                </p>

                                <p style="margin:8px 0 0; font-size:12px; line-height:19px; word-break:break-all;">
                                    <a href="{{ $url }}" style="color:#16a34a; text-decoration:none;">
                                        {{ $url }}
                                    </a>
                                </p>

                            </div>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center"
                            style="padding:24px 16px 0; color:#94a3b8; font-size:12px; line-height:19px;">

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