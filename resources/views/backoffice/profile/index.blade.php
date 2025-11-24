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
                                    <button type="submit" class="btn btn-link link-light p-0 m-0 align-baseline"><u>Renvoyer la
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
                                <div class="chat-avtar d-inline-flex mx-auto">
                                    @php
                                        $media = $user->getFirstMedia('profile_photo');
                                    @endphp

                                    <img class="rounded-circle img-fluid wid-90 img-thumbnail"
                                        src="{{ $media
                                            ? route('media.custom', ['id' => $media->id, 'filename' => $media->file_name])
                                            : URL::asset('build/images/user/avatar-1.jpg') }}"
                                    alt="Image utilisateur" />

                                    <i class="chat-badge bg-success me-2 mb-2"></i>
                                </div>
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
                                <span class="f-w-500"><i class="ph-duotone ph-clipboard-text m-r-10"></i>Modifier les informations</span>
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
                            <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                <p class="mb-0 text-muted me-1">Email</p>
                                <p class="mb-0">{{ $user->email }}</p>
                            </div>
                            <div class="d-inline-flex align-items-center justify-content-between w-100 mb-3">
                                <p class="mb-0 text-muted me-1">Téléphone</p>
                                <p class="mb-0">{{ $user->phone ?? 'Non fourni' }}</p>
                            </div>
                            <div class="d-inline-flex align-items-center justify-content-between w-100">
                                <p class="mb-0 text-muted me-1">Localisation</p>
                                <p class="mb-0">{{ $user->location ?? 'Non fourni' }}</p>
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
                                            <p class="mb-2">Pour votre sécurité, nous recommandons de changer votre mot de passe régulièrement.</p>
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
                                    <p class="mb-0">{{ $user->bio ?? 'Bonjour ! Ajoutez une bio en modifiant votre profil.' }}</p>
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
                                        <li class="list-group-item px-0 pb-0">
                                            <p class="mb-1 text-muted">Adresse</p>
                                            <p class="mb-0">{{ $user->address ?? 'Non fourni' }}</p>
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
                                                    <label class="form-label">Email <span class="text-danger">(ne peut pas être modifié)</span></label>
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
                                    <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
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
            const updateLink = document.querySelector('.update-password-tab');

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

            // Auto-activate tab if URL already contains the hash
            if (window.location.hash === '#user-set-password') {
                const tabTrigger = document.querySelector('#user-set-password-tab');
                if (tabTrigger) {
                    const bsTab = new bootstrap.Tab(tabTrigger);
                    bsTab.show();
                }
            }
        });
    </script>
@endsection