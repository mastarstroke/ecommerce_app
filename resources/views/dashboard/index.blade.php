@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="bg-light py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <h1 class="display-5 fw-bold mb-2">Welcome back, {{ $user->name }}!</h1>
                <p class="text-muted">Here's what's happening with your account today.</p>
            </div>
        </div>
    </div>
</div>

<div class="container py-4">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $user->avatar }}" 
                             class="rounded-circle" 
                             style="width: 100px; height: 100px; object-fit: cover;"
                             alt="{{ $user->name }}">
                        <button class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle" 
                                data-bs-toggle="modal" 
                                data-bs-target="#avatarModal"
                                style="width: 32px; height: 32px;">
                            <i class="fas fa-camera fa-xs"></i>
                        </button>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-2">{{ $user->email }}</p>
                    <span class="badge bg-success">{{ ucfirst($user->email_verified_at ? 'Verified' : 'Unverified') }}</span>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="list-group list-group-flush">
                    <a href="{{ route('dashboard') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-shopping-bag me-2"></i> My Orders
                    </a>
                    <a href="{{ route('profile.edit') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-user-edit me-2"></i> Profile Settings
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart me-2"></i> Wishlist
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-address-book me-2"></i> Address Book
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-bell me-2"></i> Notifications
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="col-md-9">
            <!-- Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Orders</h6>
                                    <h3 class="mb-0">{{ $stats['total_orders'] }}</h3>
                                </div>
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-shopping-cart fa-2x text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Total Spent</h6>
                                    <h3 class="mb-0">${{ number_format($stats['total_spent'], 2) }}</h3>
                                </div>
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-dollar-sign fa-2x text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-1">Completed Orders</h6>
                                    <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                                </div>
                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="fas fa-check-circle fa-2x text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Orders</h5>
                    <a href="{{ route('orders.index') }}" class="btn btn-sm btn-link">View All</a>
                </div>
                <div class="card-body p-0">
                    @if($recentOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <td>
                                            <td><strong>#{{ $order->order_number }}</strong></td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td>${{ number_format($order->total, 2) }}</td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'warning',
                                                        'processing' => 'info',
                                                        'completed' => 'success',
                                                        'declined' => 'danger',
                                                        'cancelled' => 'secondary'
                                                    ];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'light' }}">
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-sm btn-outline-primary">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                            <p class="mb-0">No orders yet. Start shopping!</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">Browse Products</a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Recommended Products -->
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">Recommended for You</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($recommendedProducts as $product)
                            <div class="col-md-4">
                                <div class="card h-100 border-0 shadow-sm">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://via.placeholder.com/150' }}" 
                                                 class="img-fluid" 
                                                 style="height: 100px; object-fit: contain;">
                                        </div>
                                        <h6 class="card-title">{{ Str::limit($product->name, 30) }}</h6>
                                        <p class="text-primary fw-bold mb-2">${{ number_format($product->price, 2) }}</p>
                                        <a href="{{ route('products.show', $product->slug) }}" class="btn btn-sm btn-outline-primary w-100">
                                            View Product
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Upload Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('profile.avatar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Update Profile Picture</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="{{ $user->avatar }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <input type="file" name="avatar" class="form-control" accept="image/*" required>
                    <small class="text-muted">Max file size: 2MB (JPG, PNG, GIF)</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection