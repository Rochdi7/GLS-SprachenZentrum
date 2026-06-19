@extends('layouts.main')

@section('title', 'Profil du Compte')
@section('breadcrumb-item', 'Utilisateurs')
@section('breadcrumb-item-active', 'Profil du Compte')

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-12">
            {{-- Success Messages for both profile and password updates --}}
            @if (session('success'))
                <div class="alert alert-success" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('status') === 'password-updated')
                <div class="alert alert-success" role="alert">
                    Mot de passe mis à jour avec succès.
                </div>
            @endif


            {{-- Email Verification Alert --}}
            @if (!$user->hasVerifiedEmail())
                <div class="card bg-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1 me-3">
                                <h3 class="text-white">Vérification de l'Email</h3>
                                <p class="text-white text-opacity-75 text-opa mb-0">Votre email n'est pas confirmé. Veuillez
                                    vérifier votre boîte de réception.
                                    {{-- Point this to your resend verification route --}}
                                <form method="POST" action="{{ route('verification.resend') }}" style="display: inline;">
                                    @csrf
                                    <button type="submit"
                                        class="btn btn-link link-light p-0 m-0 align-baseline"><u>Renvoyer la
                                            confirmation</u></button>
                                </form>
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                <img src="{{ URL::asset('build/images/application/img-accout-alert.png') }}" alt="img"
                                    class="img-fluid wid-80" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-lg-5 col-xxl-3">
                    <div class="card overflow-hidden">
                        <div class="card-body position-relative">
                            <div class="text-center mt-3">
                                <div class="d-inline-flex mx-auto position-relative" style="width:90px;height:90px;">
                                    @php
                                        $user = Auth::user();
                                        $media = $user?->getFirstMedia('profile_photo');
                                    @endphp

                                    <img class="rounded-circle img-fluid img-thumbnail"
                                        id="avatar-preview"
                                        src="{{ $media
                                            ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                                            : URL::asset('build/images/user/avatar-1.jpg') }}"
                                        alt="Image utilisateur"
                                        style="aspect-ratio:1/1;object-fit:cover;width:90px;height:90px;" />

                                    <button type="button"
                                        data-bs-toggle="modal" data-bs-target="#avatarPickerModal"
                                        title="Changer la photo"
                                        style="position:absolute;bottom:2px;right:2px;width:26px;height:26px;border-radius:50%;background:#4680ff;border:2px solid #fff;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 6px rgba(0,0,0,.25);">
                                        <i class="ph-duotone ph-camera" style="font-size:12px;color:#fff;line-height:1;"></i>
                                    </button>
                                </div>

                                {{-- Avatar Picker Modal --}}
                                <div class="modal fade" id="avatarPickerModal" tabindex="-1" aria-labelledby="avatarPickerLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="avatarPickerLabel">
                                                    <i class="ph-duotone ph-user-circle me-2 text-primary"></i>Choisir un avatar
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                {{-- Preset avatars grid --}}
                                                <p class="text-muted small mb-3">Sélectionnez un avatar prédéfini :</p>
                                                <div class="d-flex flex-wrap gap-3 justify-content-center mb-4" id="preset-grid">
                                                    @for($i = 1; $i <= 13; $i++)
                                                    <img src="{{ URL::asset('build/images/user/avatar-' . $i . '.jpg') }}"
                                                         data-index="{{ $i }}"
                                                         class="avatar-option rounded-circle"
                                                         style="width:60px;height:60px;object-fit:cover;cursor:pointer;border:3px solid transparent;transition:border-color .2s,transform .2s;"
                                                         alt="Avatar {{ $i }}"
                                                         title="Avatar {{ $i }}">
                                                    @endfor
                                                </div>

                                                <hr class="my-3">

                                                {{-- Upload custom --}}
                                                <p class="text-muted small mb-2">Ou télécharger une photo personnalisée :</p>
                                                <input type="file" id="modal-file-input" accept="image/*" class="form-control form-control-sm">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Annuler</button>
                                                <button type="button" class="btn btn-primary btn-sm" id="apply-avatar-btn" disabled>
                                                    <i class="ph-duotone ph-check me-1"></i>Appliquer
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Hidden forms --}}
                                <form id="preset-avatar-form" method="POST" action="{{ route('profile.update') }}" class="d-none">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="hidden" name="preset_avatar" id="preset-avatar-value">
                                </form>
                                <form id="upload-avatar-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="d-none">
                                    @csrf
                                    <input type="hidden" name="name" value="{{ $user->name }}">
                                    <input type="file" id="upload-avatar-input" name="profile_photo">
                                </form>

                                <script>
                                (function () {
                                    let selectedPreset = null;
                                    let selectedFile   = null;

                                    // Preset grid selection
                                    document.querySelectorAll('.avatar-option').forEach(img => {
                                        img.addEventListener('click', function () {
                                            document.querySelectorAll('.avatar-option').forEach(i => {
                                                i.style.borderColor = 'transparent';
                                                i.style.transform = 'scale(1)';
                                            });
                                            this.style.borderColor = '#4680ff';
                                            this.style.transform = 'scale(1.1)';
                                            selectedPreset = this.dataset.index;
                                            selectedFile = null;
                                            document.getElementById('modal-file-input').value = '';
                                            document.getElementById('apply-avatar-btn').disabled = false;
                                        });
                                    });

                                    // File input
                                    document.getElementById('modal-file-input').addEventListener('change', function () {
                                        if (!this.files.length) return;
                                        selectedFile = this.files[0];
                                        selectedPreset = null;
                                        document.querySelectorAll('.avatar-option').forEach(i => {
                                            i.style.borderColor = 'transparent';
                                            i.style.transform = 'scale(1)';
                                        });
                                        document.getElementById('apply-avatar-btn').disabled = false;
                                    });

                                    // Apply button
                                    document.getElementById('apply-avatar-btn').addEventListener('click', function () {
                                        if (selectedPreset) {
                                            document.getElementById('preset-avatar-value').value = selectedPreset;
                                            // Live preview
                                            document.getElementById('avatar-preview').src =
                                                '{{ URL::asset('build/images/user/avatar-') }}' + selectedPreset + '.jpg';
                                            document.getElementById('preset-avatar-form').submit();
                                        } else if (selectedFile) {
                                            // Transfer file to upload form input
                                            const dt = new DataTransfer();
                                            dt.items.add(selectedFile);
                                            document.getElementById('upload-avatar-input').files = dt.files;
                                            // Live preview
                                            const reader = new FileReader();
                                            reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
                                            reader.readAsDataURL(selectedFile);
                                            document.getElementById('upload-avatar-form').submit();
                                        }
                                    });

                                    // Reset state when modal closes
                                    document.getElementById('avatarPickerModal').addEventListener('hidden.bs.modal', function () {
                                        selectedPreset = null;
                                        selectedFile = null;
                                        document.querySelectorAll('.avatar-option').forEach(i => {
                                            i.style.borderColor = 'transparent';
                                            i.style.transform = 'scale(1)';
                                        });
                                        document.getElementById('modal-file-input').value = '';
                                        document.getElementById('apply-avatar-btn').disabled = true;
                                    });
                                })();
                                </script>
                                <h5 class="mb-0">{{ $user->name }}</h5>
                                <p class="text-muted text-sm">
                                    Contactez <a href="mailto:{{ $user->email }}"
                                        class="link-primary">{{ $user->email }}</a> 😍
                                </p>
                                <ul class="list-inline mx-auto my-4">
                                    {{-- These can be made dynamic --}}
                                </ul>
                                <div class="row g-3">
                                    {{-- These can be made dynamic --}}
                                </div>
                            </div>
                        </div>
                        {{-- [MODIFIED] Navigation with removed tabs --}}
                        <div class="nav flex-column nav-pills list-group list-group-flush account-pills mb-0"
                            id="user-set-tab" role="tablist" aria-orientation="vertical">
                            <a class="nav-link list-group-item list-group-item-action active" id="user-set-profile-tab"
                                data-bs-toggle="pill" href="#user-set-profile" role="tab"
                                aria-controls="user-set-profile" aria-selected="true">
                                <span class="f-w-500"><i class="ph-duotone ph-user-circle m-r-10"></i>Vue d'ensemble</span>
                            </a>
                            <a class="nav-link list-group-item list-group-item-action" id="user-set-information-tab"
                                data-bs-toggle="pill" href="#user-set-information" role="tab"
                                aria-controls="user-set-information" aria-selected="false">
                                <span class="f-w-500"><i class="ph-duotone ph-clipboard-text m-r-10"></i>Modifier les
                                    informations</span>
                            </a>
                            <a class="nav-link list-group-item list-group-item-action" id="user-set-password-tab"
                                data-bs-toggle="pill" href="#user-set-password" role="tab"
                                aria-controls="user-set-password" aria-selected="false">
                                <span class="f-w-500"><i class="ph-duotone ph-key m-r-10"></i>Changer le mot de passe</span>
                            </a>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h5>Informations personnelles</h5>
                        </div>
                        <div class="card-body position-relative">
                            <div class="d-flex align-items-start justify-content-between w-100 mb-3 gap-2">
                                <p class="mb-0 text-muted me-1 flex-shrink-0">Email</p>
                                <p class="mb-0 text-break text-end" style="min-width:0;">{{ $user->email }}</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between w-100 mb-3">
                                <p class="mb-0 text-muted me-1">Téléphone</p>
                                <p class="mb-0">{{ $user->phone ?? 'Non fourni' }}</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <p class="mb-0 text-muted me-1">Dernière connexion</p>
                                <p class="mb-0">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7 col-xxl-9">
                    <div class="tab-content" id="user-set-tabContent">
                        {{-- Profile Overview Tab --}}
                        <div class="tab-pane fade show active" id="user-set-profile" role="tabpanel"
                            aria-labelledby="user-set-profile-tab">
                            <div class="card alert alert-warning p-0">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1 me-3">
                                            <h4 class="alert-heading">Changez votre mot de passe</h4>
                                            <p class="mb-2">Pour votre sécurité, nous recommandons de changer votre mot de
                                                passe régulièrement.</p>
                                            <a href="#user-set-password" class="alert-link update-password-tab"
                                                role="tab">
                                                <u>Mettez à jour votre mot de passe maintenant</u>
                                            </a>

                                        </div>
                                        <div class="flex-shrink-0">
                                            <img src="{{ URL::asset('build/images/application/img-accout-password-alert.png') }}"
                                                alt="Alerte mot de passe" class="img-fluid wid-80" />
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="card">
                                <div class="card-header">
                                    <h5>À propos de moi</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">
                                        {{ $user->bio ?? 'Bonjour ! Ajoutez une bio en modifiant votre profil.' }}</p>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-header">
                                    <h5>Détails personnels</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item px-0 pt-0">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted">Nom complet</p>
                                                    <p class="mb-0">{{ $user->name }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted">Email</p>
                                                    <p class="mb-0">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted">Téléphone</p>
                                                    <p class="mb-0">{{ $user->phone ?? 'Non fourni' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1 text-muted">Localisation</p>
                                                    <p class="mb-0">{{ $user->location ?? 'Non fourni' }}</p>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-group-item px-0">
                                            <p class="mb-1 text-muted">Adresse</p>
                                            <p class="mb-0">{{ $user->address ?? 'Non fourni' }}</p>
                                        </li>
                                        <li class="list-group-item px-0 pb-0">
                                            <p class="mb-1 text-muted">Dernière connexion</p>
                                            <p class="mb-0">
                                                @if($user->last_login_at)
                                                    <span title="{{ $user->last_login_at->format('d/m/Y H:i') }}">
                                                        {{ $user->last_login_at->diffForHumans() }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Jamais connecté</span>
                                                @endif
                                            </p>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        {{-- Edit Information Tab --}}
                        <div class="tab-pane fade" id="user-set-information" role="tabpanel"
                            aria-labelledby="user-set-information-tab">
                            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Informations personnelles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nom complet</label>
                                                    <input type="text"
                                                        class="form-control @error('name') is-invalid @enderror"
                                                        name="name" value="{{ old('name', $user->name) }}">
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Photo de profil</label>
                                                    <input type="file"
                                                        class="form-control @error('profile_photo') is-invalid @enderror"
                                                        name="profile_photo">
                                                    @error('profile_photo')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Bio</label>
                                                    <textarea class="form-control @error('bio') is-invalid @enderror" name="bio">{{ old('bio', $user->bio) }}</textarea>
                                                    @error('bio')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Coordonnées</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Téléphone de contact</label>
                                                    <input type="text"
                                                        class="form-control @error('phone') is-invalid @enderror"
                                                        name="phone" value="{{ old('phone', $user->phone) }}">
                                                    @error('phone')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Email <span class="text-danger">(ne peut pas
                                                            être modifié)</span></label>
                                                    <input type="email" class="form-control"
                                                        value="{{ $user->email }}" disabled>
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="mb-0">
                                                    <label class="form-label">Adresse</label>
                                                    <textarea class="form-control @error('address') is-invalid @enderror" name="address">{{ old('address', $user->address) }}</textarea>
                                                    @error('address')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end btn-page">
                                    <button type="reset" class="btn btn-outline-secondary">Annuler</button>
                                    <button type="submit" class="btn btn-primary">Mettre à jour le profil</button>
                                </div>
                            </form>
                        </div>

                        {{-- [MODIFIED] Change Password Tab --}}
                        <div class="tab-pane fade" id="user-set-password" role="tabpanel"
                            aria-labelledby="user-set-password-tab">
                            <form method="POST" action="{{ route('profile.updatePassword') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                                    <input type="password" name="current_password" id="current_password"
                                        class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" name="password" id="password"
                                        class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de
                                        passe</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation"
                                        class="form-control" required>
                                    @error('password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="card-footer text-end">
                                    <button type="submit" class="btn btn-primary">Changer le mot de passe</button>
                                </div>
                            </form>

                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const avatarModalElement = document.getElementById('avatarPickerModal');
            const profileUrl = @json(route('profile.index'));
            const updateLink = document.querySelector('.update-password-tab');

            function getAvatarModalInstance() {
                if (!avatarModalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                    return null;
                }

                return bootstrap.Modal.getOrCreateInstance(avatarModalElement);
            }

            function openAvatarModal() {
                const avatarModal = getAvatarModalInstance();
                if (!avatarModal) {
                    return;
                }

                avatarModal.show();
            }

            function isCurrentProfilePage(link) {
                try {
                    const currentUrl = new URL(window.location.href);
                    const targetUrl = new URL(link.href, window.location.origin);
                    const profilePageUrl = new URL(profileUrl, window.location.origin);

                    return currentUrl.pathname === profilePageUrl.pathname
                        && targetUrl.pathname === profilePageUrl.pathname;
                } catch (error) {
                    return false;
                }
            }

            document.querySelectorAll('.js-open-avatar-modal').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (!isCurrentProfilePage(link)) {
                        return;
                    }

                    e.preventDefault();
                    history.replaceState(null, '', '#avatar');
                    openAvatarModal();
                });
            });

            if (updateLink) {
                updateLink.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Show the password tab
                    const tabTrigger = document.querySelector('#user-set-password-tab');
                    if (tabTrigger) {
                        const bsTab = new bootstrap.Tab(tabTrigger);
                        bsTab.show();

                        // Update the URL hash without reloading
                        history.pushState(null, null, '#user-set-password');

                        // Optional: Scroll to the tab content
                        setTimeout(() => {
                            const pane = document.querySelector('#user-set-password');
                            if (pane) {
                                pane.scrollIntoView({
                                    behavior: 'smooth'
                                });
                            }
                        }, 150);
                    }
                });
            }

            // Auto-activate password tab on hash or after password update
            var activeTab = @json(session('active_tab', ''));
            if (window.location.hash === '#user-set-password' || activeTab === 'password'
                || @json($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))) {
                const tabTrigger = document.querySelector('#user-set-password-tab');
                if (tabTrigger) {
                    const bsTab = new bootstrap.Tab(tabTrigger);
                    bsTab.show();
                }
            }

            if (window.location.hash === '#avatar') {
                openAvatarModal();
            }

            window.addEventListener('hashchange', function() {
                if (window.location.hash === '#avatar') {
                    openAvatarModal();
                }
            });

            if (avatarModalElement) {
                avatarModalElement.addEventListener('hidden.bs.modal', function() {
                    if (window.location.hash === '#avatar') {
                        history.replaceState(null, '', window.location.pathname + window.location.search);
                    }
                });
            }
        });
    </script>
@endsection
