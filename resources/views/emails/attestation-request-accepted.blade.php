@component('mail::message')
# Bonjour {{ $request->first_name }} {{ $request->last_name }},

Nous avons le plaisir de vous informer que votre demande d'attestation de participation a été **acceptée**.

Notre équipe finalise actuellement votre document. Vous serez recontacté(e) prochainement pour la remise de l'attestation.

**Récapitulatif de votre demande :**
- **Groupe :** {{ $request->group_name }}
- **Niveau :** {{ $request->level }}
- **Langue :** {{ $request->language }}

Merci de votre confiance,
GLS Sprachenzentrum
@endcomponent
