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
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="admin@Gls Team.com" required autocomplete="email" autofocus id="floatingInput" placeholder="Adresse email">
                        @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                    </div>
                    <div class="form-group mb-3">
                        <input type="password" type="password" class="form-control @error('password') is-invalid @enderror" value="12345678" name="password" required autocomplete="current-password" id="floatingInput1" placeholder="Mot de passe">
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
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
@endsection
