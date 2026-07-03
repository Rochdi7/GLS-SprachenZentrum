{{-- Shared CRM class selector for period + hourly create forms --}}
<select name="crm_class_id" class="form-select class-select" required>
    <option value="">— Sélectionner une classe —</option>
    @forelse ($crmClasses as $cls)
        <option value="{{ $cls['crm_id'] }}"
            data-name="{{ $cls['name'] }}"
            data-teacher="{{ $cls['teacher'] }}"
            data-level="{{ $cls['level'] }}"
            data-last-rate="{{ $cls['last_rate'] ?? '' }}"
            {{ old('crm_class_id', $selectedCrmId) == $cls['crm_id'] ? 'selected' : '' }}>
            {{ $cls['name'] }}@if($cls['teacher'] !== '—') — {{ $cls['teacher'] }}@endif
        </option>
    @empty
        <option value="" disabled>Aucune classe disponible</option>
    @endforelse
</select>
