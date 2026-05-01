@component('mail::message')
# Nouvelle demande d'attestation

Une nouvelle demande d'attestation de participation vient d'être déposée.

**Étudiant :** {{ $request->last_name }} {{ $request->first_name }}
**Email :** {{ $request->email }}
@if($request->phone)
**Téléphone :** {{ $request->phone }}
@endif
**Date de naissance :** {{ $request->birth_date?->format('d/m/Y') ?? '—' }}
**Lieu de naissance :** {{ $request->birth_place ?? '—' }}

---

**Groupe (saisi par l'étudiant) :** {{ $request->group_name }}
**Niveau :** {{ $request->level }}
**Langue souhaitée :** {{ $request->language }}

---

@component('mail::button', ['url' => route('backoffice.attestation_requests.show', $request->id)])
Examiner la demande
@endcomponent

Merci,
GLS Sprachenzentrum
@endcomponent
