@extends('admin.layouts.admin')

@section('page-title', 'Order Management')

@section('content')
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title">Total Orders</h5>
                <h2 class="display-4">{{ $stats['total'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <h5 class="card-title">Pending Orders</h5>
                <h2 class="display-4">{{ $stats['pending'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <h5 class="card-title">Completed Orders</h5>
                <h2 class="display-4">{{ $stats['completed'] }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-info text-white">
            <div class="card-body">
                <h5 class="card-title">Total Revenue</h5>
                <h2 class="display-4">${{ number_format($stats['revenue'], 2) }}</h2>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">All Orders</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Payment Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><strong>{{ $order->order_number }}</strong></td>
                            <td>{{ $order->user->name }}</td>
                            <td>${{ number_format($order->total, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $order->getStatusBadgeAttribute() }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                                    {{ ucfirst($order->payment_status) }}
                                </span>
                            </td>
                            <td>{{ $order->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        {{ $orders->links() }}
    </div>
</div>
@endsection