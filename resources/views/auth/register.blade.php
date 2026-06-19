@extends('layouts.AuthLayout')

@section('title', 'Register')

@section('content')
    <div class="auth-form">
        <div class="card my-5">
            <div class="card-body">
                <div class="text-center">
                    <img src="{{ URL::asset('build/images/authentication/img-auth-register.png') }}" alt="images" class="img-fluid mb-3">
                    <h4 class="f-w-500 mb-1">Register with your email</h4>
                    <p class="mb-3">Already have an Account? <a href="{{ route('login') }}" class="link-primary">Log
                            in</a></p>
                </div>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
                            value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="Enter name">
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email') }}" required autocomplete="email" placeholder="Email Address">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                    <div class="form-group mb-3">
                        <div class="auth-password-field">
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password"
                                id="registerPassword" required autocomplete="new-password" placeholder="Password">
                            <button type="button"
                                class="auth-password-toggle"
                                data-password-toggle="registerPassword"
                                aria-label="Show password"
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
                        <div class="auth-password-field">
                            <input type="password" class="form-control" name="password_confirmation" required
                                id="registerPasswordConfirmation" autocomplete="new-password" placeholder="Confirm Password">
                            <button type="button"
                                class="auth-password-toggle"
                                data-password-toggle="registerPasswordConfirmation"
                                aria-label="Show password confirmation"
                                aria-pressed="false">
                                <i class="ti ti-eye-off"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary">Create Account</button>
                    </div>
                </form>
                
            </div>
        </div>
    </div>
@endsection
