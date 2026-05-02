@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="bg-primary bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-store fa-2x text-white"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold">Welcome Back!</h3>
                        <p class="text-muted">Login to access your account</p>
                    </div>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       placeholder="Enter your email"
                                       required autofocus>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       placeholder="Enter your password"
                                       required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                Forgot Password?
                            </a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                Don't have an account? 
                                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold">
                                    Create Account
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Demo Credentials -->
            <div class="card shadow-sm mt-3">
                <div class="card-body">
                    <div class="text-center">
                        <i class="fas fa-info-circle text-info"></i>
                        <small class="text-muted">Demo Credentials</small>
                    </div>
                    <div class="row small mt-2">
                        <div class="col">
                            <strong>Admin:</strong><br>
                            admin@example.com<br>
                            password
                        </div>
                        <div class="col">
                            <strong>Customer:</strong><br>
                            user@example.com<br>
                            password
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle password visibility
    $('.toggle-password').click(function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
</script>
@endpush
@endsection