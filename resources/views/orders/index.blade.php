@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-3">
            <!-- User Sidebar -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-user fa-3x text-white"></i>
                        </div>
                    </div>
                    <h5 class="mb-1">{{ Auth::user()->name }}</h5>
                    <p class="text-muted small">{{ Auth::user()->email }}</p>
                    <hr>
                    <div class="d-grid">
                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="list-group list-group-flush">
                    <a href="{{ route('orders.index') }}" class="list-group-item list-group-item-action active">
                        <i class="fas fa-shopping-bag"></i> My Orders
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-heart"></i> Wishlist
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-address-card"></i> Address Book
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0 fw-bold">My Order History</h5>
                </div>
                <div class="card-body">
                    @if($orders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order #</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Payment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>
                                                <strong class="text-primary">#{{ $order->order_number }}</strong>
                                            </td>
                                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                                            <td class="fw-bold">${{ number_format($order->total, 2) }}</td>
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
                                                    <i class="fas 
                                                        @if($order->status == 'pending') fa-clock
                                                        @elseif($order->status == 'processing') fa-spinner fa-pulse
                                                        @elseif($order->status == 'completed') fa-check-circle
                                                        @elseif($order->status == 'declined') fa-times-circle
                                                        @else fa-ban
                                                        @endif
                                                    "></i>
                                                    {{ ucfirst($order->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('orders.show', $order->order_number) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-shopping-bag fa-4x text-muted mb-3"></i>
                            <h4>No orders yet</h4>
                            <p class="text-muted">You haven't placed any orders yet.</p>
                            <a href="{{ route('products.index') }}" class="btn btn-primary">
                                Start Shopping
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection