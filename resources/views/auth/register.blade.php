@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="bg-success bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-user-plus fa-2x text-white"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold">Create Account</h3>
                        <p class="text-muted">Join ShopHub for exclusive deals!</p>
                    </div>
                    
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text" name="name" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name') }}" 
                                           placeholder="Enter your full name"
                                           required>
                                </div>
                                @error('name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
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
                                       required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phone Number (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-phone text-muted"></i>
                                </span>
                                <input type="tel" name="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" 
                                       placeholder="Enter your phone number">
                            </div>
                            @error('phone')
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
                                       placeholder="Create a password"
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-check-circle text-muted"></i>
                                </span>
                                <input type="password" name="password_confirmation" 
                                       class="form-control" 
                                       placeholder="Confirm your password"
                                       required>
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="terms" id="terms" required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="#" class="text-decoration-none">Terms of Service</a> and 
                                <a href="#" class="text-decoration-none">Privacy Policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0 text-muted">
                                Already have an account? 
                                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold">
                                    Sign In
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection