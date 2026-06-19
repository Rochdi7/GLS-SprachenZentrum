@extends('layouts.AuthLayout')

@section('title', 'Login')

@section('content')
    <div class="auth-form">
        <div class="card my-5">
            <div class="card-body">
                <div class="text-center">
                    <img src="{{ URL::asset('assets/images/logo/gls.png') }}" alt="GLS Logo" class="mb-3" style="width:120px;object-fit:contain;">
                    <h4 class="f-w-500 mb-1">Connectez-vous avec votre email</h4>
                </div>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    {{-- Email --}}
                    <div class="form-group mb-3">
                        <input type="email"
                               class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}"
                               required autocomplete="email" autofocus
                               placeholder="Adresse email">
                        @error('email')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="form-group mb-3">
                        <div class="auth-password-field">
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="current-password"
                                   id="loginPassword" placeholder="Mot de passe">
                            <button type="button"
                                    class="auth-password-toggle"
                                    data-password-toggle="loginPassword"
                                    aria-label="Afficher le mot de passe"
                                    aria-pressed="false">
                                <i class="ti ti-eye-off"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="d-flex mt-1 justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input input-primary" type="checkbox"
                                   name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label text-muted" for="remember">Se souvenir de moi</label>
                        </div>
                        <a href="{{ route('password.request') }}">
                            <h6 class="f-w-400 mb-0">Mot de passe oublié ?</h6>
                        </a>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Se connecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
