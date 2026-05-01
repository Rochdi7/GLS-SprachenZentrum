@component('mail::message')
# Bonjour {{ $request->first_name }} {{ $request->last_name }},

Nous vous remercions pour votre demande d'attestation de participation. Après vérification, nous ne sommes malheureusement pas en mesure d'y donner suite.

**Motif :**
{{ $request->refusal_reason }}

Si vous pensez qu'il s'agit d'une erreur ou si vous souhaitez plus d'informations, n'hésitez pas à nous contacter à info@glssprachenzentrum.ma.

Cordialement,
GLS Sprachenzentrum
@endcomponent
