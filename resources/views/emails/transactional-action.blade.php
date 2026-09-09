<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $mailSubject }}</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,sans-serif;color:#0f172a;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;">
                    <tr>
                        <td style="padding:28px 32px;border-bottom:1px solid #e2e8f0;">
                            <div style="font-size:18px;font-weight:700;">Medical Evidence Contact</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.3;">{{ $heading }}</h1>
                            <p style="margin:0 0 20px;font-size:15px;line-height:1.7;color:#475569;">{{ $intro }}</p>

                            @if (! empty($details))
                                <div style="margin:0 0 24px;padding:16px 18px;background:#f8fafc;border-radius:12px;">
                                    @foreach ($details as $detail)
                                        <p style="margin:{{ $loop->first ? '0' : '8px' }} 0 0;font-size:14px;line-height:1.5;color:#334155;">{{ $detail }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 18px;background:#0f766e;color:#ffffff;text-decoration:none;border-radius:10px;font-weight:700;font-size:14px;">{{ $actionLabel }}</a>

                            @if ($secondaryActionLabel && $secondaryActionUrl)
                                <a href="{{ $secondaryActionUrl }}" style="display:inline-block;margin-left:8px;padding:12px 18px;background:#ffffff;color:#334155;text-decoration:none;border:1px solid #cbd5e1;border-radius:10px;font-weight:700;font-size:14px;">{{ $secondaryActionLabel }}</a>
                            @endif

                            <p style="margin:24px 0 0;font-size:12px;line-height:1.6;color:#94a3b8;">Questa email è generata automaticamente dalla piattaforma.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
