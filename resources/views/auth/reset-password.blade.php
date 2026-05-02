@extends('layouts.app')

@section('title', 'Reset Password')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="bg-info bg-gradient rounded-circle d-inline-flex align-items-center justify-content-center" 
                                 style="width: 60px; height: 60px;">
                                <i class="fas fa-lock-open fa-2x text-white"></i>
                            </div>
                        </div>
                        <h3 class="fw-bold">Reset Password</h3>
                        <p class="text-muted">Create a new password for your account</p>
                    </div>
                    
                    <form method="POST" action="{{ route('password.update') }}">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-envelope text-muted"></i>
                                </span>
                                <input type="email" name="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-lock text-muted"></i>
                                </span>
                                <input type="password" name="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       required>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">
                                    <i class="fas fa-check-circle text-muted"></i>
                                </span>
                                <input type="password" name="password_confirmation" 
                                       class="form-control" 
                                       required>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2">
                            <i class="fas fa-save me-2"></i>Reset Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection