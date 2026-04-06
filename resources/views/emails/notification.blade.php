<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifikasi Sistem</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f8; margin: 0; padding: 0;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f4f6f8; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); overflow: hidden;">
                    <tr>
                        <td align="center" style="background-color: #0d9488; padding: 20px;">
                            <h2 style="color: #ffffff; margin: 0;">{{ config('app.name') }}</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 30px;">
                            <h3 style="color: #333333; margin-top: 0;">Halo,</h3>
                            <p style="color: #555555; line-height: 1.6;">
                                {!! $messageStr !!}
                            </p>
                            
                            @if($actionUrl)
                            <div style="text-align: center; margin-top: 30px;">
                                <a href="{{ $actionUrl }}" style="background-color: #0d9488; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">
                                    Buka di Aplikasi
                                </a>
                            </div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="background-color: #f8f9fa; padding: 15px; border-top: 1px solid #eeeeee;">
                            <p style="color: #888888; font-size: 12px; margin: 0;">
                                Email ini dikirim secara otomatis oleh {{ config('app.name') }}. Mohon tidak membalas email ini.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
