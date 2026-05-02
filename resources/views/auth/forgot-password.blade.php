@extends('layouts.app')

@section('title', 'Forgot Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="bg-warning bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-key fa-2x text-white"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold">Forgot Password?</h3>
                        <p class="text-muted">Enter your email to reset your password</p>
                    </div>
                    
                    @if(session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        </div>
                    @endif
                    
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       placeholder="Enter your registered email"
                                       required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-paper-plane me-2"></i>Send Reset Link
                        </button>
                        
                        <div class="text-center">
                            <a href="{{ route('login') }}" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>Back to Login
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection