@php
    // $logoCid is provided by App\Mail\Concerns\EmbedsBrandLogo (Symfony embed).
    $logoCid = $logoCid ?? asset('assets/images/logo/gls.png');
    $year = now()->year;
@endphp
<!DOCTYPE html>
<html lang="fr" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $title ?? 'GLS Sprachenzentrum' }}</title>
    <!--[if mso]>
    <style type="text/css">
        table, td, div, h1, h2, h3, p { font-family: Arial, Helvetica, sans-serif !important; }
    </style>
    <![endif]-->
</head>

<body
    style="margin:0;padding:0;background:#f4f1ea;font-family:'Segoe UI',Roboto,Arial,Helvetica,sans-serif;color:#211e1d;-webkit-font-smoothing:antialiased;">

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
        style="background:#f4f1ea;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                {{-- ========== Outer card ========== --}}
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0"
                    style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 6px 24px rgba(28,30,40,.08);">

                    {{-- ===== German tricolor accent strip ===== --}}
                    <tr>
                        <td style="line-height:0;font-size:0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td height="6" width="33.33%"
                                        style="background:#000000;height:6px;line-height:6px;font-size:0;">&nbsp;</td>
                                    <td height="6" width="33.33%"
                                        style="background:#dd0000;height:6px;line-height:6px;font-size:0;">&nbsp;</td>
                                    <td height="6" width="33.33%"
                                        style="background:#ffce00;height:6px;line-height:6px;font-size:0;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ===== Header (logo + brand name) ===== --}}
                    <tr>
                        <td align="center" style="padding:32px 32px 20px 32px;background:#ffffff;">
                            <img src="{{ $logoCid }}" alt="GLS Sprachenzentrum" width="160"
                                style="display:block;width:160px;max-width:60%;height:auto;border:0;outline:none;">
                            <div
                                style="margin-top:14px;font-size:13px;letter-spacing:2px;text-transform:uppercase;color:#7a716c;font-weight:600;">
                                Deutsch · Maroc · seit 2008
                            </div>
                        </td>
                    </tr>

                    {{-- ===== Title bar ===== --}}
                    @isset($title)
                        <tr>
                            <td style="padding:0 32px;">
                                <div style="border-top:1px solid #ece6d8;"></div>
                            </td>
                        </tr>
                        <tr>
                            <td align="center" style="padding:24px 32px 8px 32px;">
                                <h1
                                    style="margin:0;font-size:22px;line-height:1.3;color:#181615;font-weight:700;letter-spacing:-.2px;">
                                    {{ $title }}
                                </h1>
                                @isset($subtitle)
                                    <p style="margin:8px 0 0 0;font-size:14px;color:#6e6660;">{{ $subtitle }}</p>
                                @endisset
                            </td>
                        </tr>
                    @endisset

                    {{-- ===== Body ===== --}}
                    <tr>
                        <td style="padding:24px 36px 36px 36px;font-size:15px;line-height:1.65;color:#2d2926;">
                            {{ $slot }}
                        </td>
                    </tr>

                    {{-- ===== Footer ===== --}}
                    <tr>
                        <td
                            style="background:#181615;padding:28px 32px;color:#cfc8bf;font-size:12.5px;line-height:1.6;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td align="left" style="vertical-align:top;">
                                        <div
                                            style="font-size:14px;color:#ffffff;font-weight:700;letter-spacing:.5px;margin-bottom:6px;">
                                            GLS Sprachenzentrum
                                        </div>
                                        <div>Centre de langue allemande au Maroc</div>
                                        <div style="margin-top:10px;">
                                            <a href="mailto:info@gls-sprachzentrum.ma"
                                                style="color:#ffce00;text-decoration:none;">info@gls-sprachzentrum.ma</a>
                                        </div>
                                    </td>
                                    <td align="right" style="vertical-align:top;">
                                        <a href="https://www.glssprachenzentrum.ma"
                                            style="color:#ffffff;text-decoration:none;font-weight:600;">glssprachenzentrum.ma</a>
                                        <div style="margin-top:8px;color:#7a716c;">© {{ $year }} GLS · Tous
                                            droits réservés</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ===== Moroccan red accent strip ===== --}}
                    <tr>
                        <td height="4" style="background:#c1272d;line-height:4px;font-size:0;">&nbsp;</td>
                    </tr>
                </table>

                <div style="max-width:600px;margin:18px auto 0;font-size:11px;color:#9a918a;line-height:1.5;">
                    Cet email a été envoyé automatiquement, merci de ne pas y répondre directement.
                </div>

            </td>
        </tr>
    </table>

</body>

</html>
