@component('emails.layouts.branded', [
    'title' => "Suite à votre demande d'attestation",
    'subtitle' => 'GLS Sprachenzentrum',
])
    <p style="margin:0 0 14px 0;font-size:16px;">
        Bonjour <strong>{{ $request->first_name }} {{ $request->last_name }}</strong>,
    </p>

    <p style="margin:0 0 14px 0;">
        Nous vous remercions pour votre demande d'attestation de participation. Après vérification, nous ne sommes
        malheureusement pas en mesure d'y donner suite.
    </p>

    <div style="background:#fdf3f3;border-left:4px solid #c1272d;padding:14px 18px;border-radius:6px;margin:18px 0;">
        <div
            style="font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#922024;font-weight:600;margin-bottom:6px;">
            Motif</div>
        <div style="color:#3a1517;font-size:14.5px;">{{ $request->refusal_reason }}</div>
    </div>

    <p style="margin:0 0 8px 0;">
        Si vous pensez qu'il s'agit d'une erreur ou souhaitez plus d'informations, n'hésitez pas à nous contacter à
        <a href="mailto:info@gls-sprachzentrum.ma"
            style="color:#1c45db;text-decoration:none;font-weight:600;">info@gls-sprachzentrum.ma</a>.
    </p>

    <p style="margin:24px 0 0 0;">
        Cordialement,<br>
        <strong>L'équipe GLS Sprachenzentrum</strong>
    </p>
@endcomponent
