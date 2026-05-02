@extends('layouts.app')

@section('title', 'Order #' . $order->order_number)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-md-12">
            <!-- Order Header -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-1">Order #{{ $order->order_number }}</h4>
                            <p class="text-muted mb-0">
                                Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}
                            </p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'declined' => 'danger',
                                    'cancelled' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'light' }} fs-6 px-3 py-2">
                                <i class="fas 
                                    @if($order->status == 'pending') fa-clock
                                    @elseif($order->status == 'processing') fa-spinner fa-pulse
                                    @elseif($order->status == 'completed') fa-check-circle
                                    @elseif($order->status == 'declined') fa-times-circle
                                    @else fa-ban
                                    @endif
                                "></i>
                                {{ strtoupper($order->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Order Items -->
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">Order Items</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th class="text-end">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->items as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        @if($item->product && $item->product->image)
                                                            <img src="{{ asset('storage/' . $item->product->image) }}" 
                                                                 class="rounded me-3" 
                                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                                        @else
                                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" 
                                                                 style="width: 50px; height: 50px;">
                                                                <i class="fas fa-image text-muted"></i>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <h6 class="mb-0">{{ $item->product->name ?? 'Product Unavailable' }}</h6>
                                                            @if($item->attributes)
                                                                <small class="text-muted">
                                                                    @foreach(json_decode($item->attributes, true) as $key => $value)
                                                                        {{ ucfirst($key) }}: {{ $value }} 
                                                                    @endforeach
                                                                </small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                                <td>{{ $item->quantity }}</td>
                                                <td class="text-end fw-bold">${{ number_format($item->total, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-light">
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                            <td class="text-end">${{ number_format($order->subtotal, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Tax (10%):</td>
                                            <td class="text-end">${{ number_format($order->tax, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-end fw-bold">Shipping:</td>
                                            <td class="text-end">${{ number_format($order->shipping_cost, 2) }}</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td colspan="3" class="text-end fs-5 fw-bold">Total:</td>
                                            <td class="text-end fs-5 fw-bold text-primary">
                                                ${{ number_format($order->total, 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Timeline -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">Order Timeline</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success rounded-circle p-2">
                                            <i class="fas fa-check text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Order Placed</h6>
                                        <small class="text-muted">{{ $order->created_at->format('F d, Y h:i A') }}</small>
                                    </div>
                                </div>
                                
                                @if(in_array($order->status, ['processing', 'completed']))
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-info rounded-circle p-2">
                                            <i class="fas fa-spinner fa-pulse text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Order Processing</h6>
                                        <small class="text-muted">{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                                
                                @if($order->status == 'completed')
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-success rounded-circle p-2">
                                            <i class="fas fa-truck text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Order Delivered</h6>
                                        <small class="text-muted">{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                                
                                @if($order->status == 'cancelled')
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0">
                                        <div class="bg-danger rounded-circle p-2">
                                            <i class="fas fa-ban text-white"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="mb-0">Order Cancelled</h6>
                                        <small class="text-muted">{{ $order->updated_at->format('F d, Y h:i A') }}</small>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Order Information Sidebar -->
                <div class="col-lg-4">
                    <!-- Shipping Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-truck"></i> Shipping Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold">Shipping Address</h6>
                            <p class="text-muted mb-3">
                                {{ $order->shipping_address }}
                            </p>
                            
                            <h6 class="fw-bold">Payment Method</h6>
                            <p class="text-muted mb-0">
                                @if($order->payment_method == 'credit_card')
                                    <i class="fab fa-cc-visa"></i> Credit Card
                                @elseif($order->payment_method == 'paypal')
                                    <i class="fab fa-paypal"></i> PayPal
                                @else
                                    <i class="fas fa-money-bill"></i> Cash on Delivery
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <!-- Billing Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-file-invoice"></i> Billing Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold">Billing Address</h6>
                            <p class="text-muted">
                                {{ $order->billing_address }}
                            </p>
                            
                            <h6 class="fw-bold">Payment Status</h6>
                            <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }} fs-6">
                                {{ ucfirst($order->payment_status) }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Order Notes -->
                    @if($order->notes)
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-sticky-note"></i> Order Notes
                            </h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-0">{{ $order->notes }}</p>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Action Buttons -->
                    <div class="mt-4">
                        @if($order->status == 'pending')
                            <form action="{{ route('orders.cancel', $order->order_number) }}" 
                                  method="POST" 
                                  onsubmit="return confirm('Are you sure you want to cancel this order?')">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-times-circle"></i> Cancel Order
                                </button>
                            </form>
                        @endif
                        
                        @if($order->status == 'completed')
                            <a href="#" class="btn btn-outline-primary w-100">
                                <i class="fas fa-star"></i> Write a Review
                            </a>
                        @endif
                        
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100 mt-2">
                            <i class="fas fa-shopping-cart"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
}
.timeline:before {
    content: '';
    position: absolute;
    left: 20px;
    top: 20px;
    bottom: 20px;
    width: 2px;
    background: #dee2e6;
}
.timeline > .d-flex {
    position: relative;
    z-index: 1;
}
.timeline > .d-flex .flex-shrink-0 {
    background: white;
}
</style>
@endsection