@extends('layouts.app')

@section('title', 'Profile Settings')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <!-- Same sidebar as dashboard -->
            <div class="card shadow-sm">
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-user-edit me-2"></i> Profile Settings
                    </a>
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i> My Orders
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Profile Settings</h5>
                </div>
                <div class="card-body">
                    <ul class="nav nav-tabs" id="profileTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">
                                Profile Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button" role="tab">
                                Change Password
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content mt-4">
                        <!-- Profile Information Tab -->
                        <div class="tab-pane fade show active" id="profile" role="tabpanel">
                            <form action="{{ route('profile.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                           value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                           value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" 
                                           value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Default Address</label>
                                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" 
                                              rows="3">{{ old('address', $user->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Update Profile</button>
                            </form>
                        </div>
                        
                        <!-- Change Password Tab -->
                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <form action="{{ route('profile.password') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" required>
                                </div>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Password must be at least 8 characters long.
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Change Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection