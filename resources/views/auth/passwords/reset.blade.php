@extends('layouts.AuthLayout')

@section('title', 'Reset Password')

@section('content')
    <div class="auth-form">
        <div class="card my-5">
            <div class="card-body">
                <div class="text-center">
                    <img src="{{ URL::asset('assets/images/logo/gls.png') }}" alt="GLS Logo" class="mb-3" style="width:120px;object-fit:contain;">
                    <h4 class="f-w-500 mb-1">Reset password</h4>
                    <p class="mb-3">Back to <a href="{{ route('login') }}" class="link-primary ms-1">Log in</a></p>
                </div>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-group mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               name="email" value="{{ $email ?? old('email') }}" required
                               autocomplete="email" autofocus placeholder="Adresse email">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">New Password</label>
                        <div class="auth-password-field">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password"
                                   id="resetPassword" placeholder="Nouveau mot de passe">
                            <button type="button" class="auth-password-toggle"
                                    data-password-toggle="resetPassword"
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

                    <div class="form-group mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="auth-password-field">
                            <input type="password" class="form-control"
                                   name="password_confirmation" required
                                   autocomplete="new-password"
                                   id="resetConfirm" placeholder="Confirmer le mot de passe">
                            <button type="button" class="auth-password-toggle"
                                    data-password-toggle="resetConfirm"
                                    aria-label="Afficher la confirmation du mot de passe"
                                    aria-pressed="false">
                                <i class="ti ti-eye-off"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
