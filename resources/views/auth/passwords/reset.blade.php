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
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror"
                                   name="password" required autocomplete="new-password"
                                   id="resetPassword" placeholder="Nouveau mot de passe">
                            <button type="button" class="input-group-text bg-white border-start-0"
                                    id="toggleResetPassword" tabindex="-1"
                                    style="cursor:pointer;border-color:#dee2e6;">
                                <i id="toggleResetIcon" class="ti ti-eye-off" style="font-size:1.1rem;color:#6c757d;"></i>
                            </button>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Confirm Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control"
                                   name="password_confirmation" required
                                   autocomplete="new-password"
                                   id="resetConfirm" placeholder="Confirmer le mot de passe">
                            <button type="button" class="input-group-text bg-white border-start-0"
                                    id="toggleConfirm" tabindex="-1"
                                    style="cursor:pointer;border-color:#dee2e6;">
                                <i id="toggleConfirmIcon" class="ti ti-eye-off" style="font-size:1.1rem;color:#6c757d;"></i>
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

    <script>
    function toggleField(btnId, inputId, iconId) {
        document.getElementById(btnId).addEventListener('click', function () {
            var input = document.getElementById(inputId);
            var icon  = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('ti-eye-off', 'ti-eye');
            } else {
                input.type = 'password';
                icon.classList.replace('ti-eye', 'ti-eye-off');
            }
        });
    }
    toggleField('toggleResetPassword', 'resetPassword', 'toggleResetIcon');
    toggleField('toggleConfirm', 'resetConfirm', 'toggleConfirmIcon');
    </script>
@endsection
