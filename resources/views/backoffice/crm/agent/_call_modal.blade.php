{{--
    Call note / follow-up modal.
    Included in all agent dashboard views.
    Expects @section('scripts') to inject the student data on btn-call click.
--}}
<div class="modal fade" id="callModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('backoffice.crm.agent.follow-ups.save') }}">
                @csrf
                <input type="hidden" name="crm_student_id"  id="callStudentId">
                <input type="hidden" name="registration_id" id="callRegistrationId">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ph-duotone ph-phone me-2 text-primary"></i>
                        Note d'appel — <span id="modalStudentName" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(\App\Models\AgentFollowUp::STATUSES as $val => $lbl)
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Agent</label>
                        <select name="agent_id" class="form-select">
                            <option value="">— Moi-même ({{ auth()->user()->name }}) —</option>
                            @foreach(\App\Models\User::orderBy('name')->get(['id','name']) as $u)
                                <option value="{{ $u->id }}" {{ auth()->id() === $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Note</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Résumé de l'appel, réaction de l'étudiant..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Date de relance</label>
                        <input type="date" name="follow_up_date" class="form-control"
                               min="{{ today()->toDateString() }}"
                               value="{{ today()->addDays(3)->toDateString() }}">
                        <div class="form-text">Laisser vide si aucune relance prévue.</div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ph-duotone ph-floppy-disk me-1"></i> Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
