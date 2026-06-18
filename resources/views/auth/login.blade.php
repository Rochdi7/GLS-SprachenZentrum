@extends('layouts.AuthLayout')

@section('title', 'Login')

@section('content')
    <div class="auth-form">
        <div class="card my-5">
            <div class="card-body">
                <div class="text-center">
                    <img src="{{ URL::asset('assets/images/logo/gls.png') }}" alt="GLS Logo" class="mb-3" style="width: 120px; object-fit: contain;">
                    <h4 class="f-w-500 mb-1">Connectez-vous avec votre email</h4>
                    
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus id="floatingInput" placeholder="Adresse email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    </div>
                    <div class="form-group mb-3">
                        <div class="input-group">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" id="floatingInput1" placeholder="Mot de passe">
                            <button type="button" class="input-group-text bg-white border-start-0" id="togglePassword" tabindex="-1" style="cursor:pointer;border-color:#dee2e6;">
                                <i id="toggleIcon" class="ti ti-eye-off" style="font-size:1.1rem;color:#6c757d;"></i>
                            </button>
                            @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="d-flex mt-1 justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input input-primary" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
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
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    var input = document.getElementById('floatingInput1');
    var icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('ti-eye-off', 'ti-eye');
    } else {
        input.type = 'password';
        icon.classList.replace('ti-eye', 'ti-eye-off');
    }
});
</script>
@endsection
