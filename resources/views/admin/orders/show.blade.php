@extends('admin.layouts.admin')

@section('page-title', 'Order Details #' . $order->order_number)

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Order #{{ $order->order_number }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Order Items -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Order Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($item->product && $item->product->images && count($item->product->images) > 0)
                                            <img src="{{ asset('storage/' . $item->product->images[0]) }}" 
                                                 class="rounded me-2" 
                                                 style="width: 50px; height: 50px; object-fit: cover;">
                                        @else
                                            <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 50px; height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $item->product ? $item->product->name : 'Product Deleted' }}</strong>
                                            <br>
                                            <small class="text-muted">SKU: {{ $item->product ? $item->product->sku : 'N/A' }}</small>
                                            @if($item->attributes)
                                                <br>
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
                                <td class="fw-bold">${{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                <td>${{ number_format($order->subtotal, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Tax (10%):</td>
                                <td>${{ number_format($order->tax, 2) }}</td>
                            </tr>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Shipping:</td>
                                <td>${{ number_format($order->shipping_cost, 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td colspan="3" class="text-end fs-5 fw-bold">Total:</td>
                                <td class="fs-5 fw-bold text-primary">${{ number_format($order->total, 2) }}</td>
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
    
    <div class="col-md-4">
        <!-- Customer Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-user"></i> Customer Information
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <img src="{{ $order->user->avatar }}" class="rounded-circle me-3" style="width: 50px; height: 50px;">
                    <div>
                        <h6 class="mb-0">{{ $order->user->name }}</h6>
                        <small class="text-muted">{{ $order->user->email }}</small>
                    </div>
                </div>
                <hr>
                <div class="mb-2">
                    <i class="fas fa-phone"></i> <strong>Phone:</strong><br>
                    {{ $order->user->phone ?? 'Not provided' }}
                </div>
                <div class="mb-2">
                    <i class="fas fa-calendar"></i> <strong>Customer Since:</strong><br>
                    {{ $order->user->created_at->format('M d, Y') }}
                </div>
                <div class="mb-2">
                    <i class="fas fa-shopping-bag"></i> <strong>Total Orders:</strong><br>
                    {{ $order->user->orders()->count() }}
                </div>
            </div>
        </div>
        
        <!-- Shipping Information -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-truck"></i> Shipping Information
                </h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $order->shipping_address }}</p>
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
                <p class="mb-0">{{ $order->billing_address }}</p>
                <hr>
                <div class="mb-2">
                    <strong>Payment Method:</strong><br>
                    @if($order->payment_method == 'credit_card')
                        <i class="fab fa-cc-visa"></i> Credit Card
                    @elseif($order->payment_method == 'paypal')
                        <i class="fab fa-paypal"></i> PayPal
                    @else
                        <i class="fas fa-money-bill"></i> Cash on Delivery
                    @endif
                </div>
                <div>
                    <strong>Payment Status:</strong><br>
                    <span class="badge bg-{{ $order->payment_status == 'paid' ? 'success' : 'warning' }}">
                        {{ ucfirst($order->payment_status) }}
                    </span>
                </div>
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
                <p class="mb-0">{{ $order->notes }}</p>
            </div>
        </div>
        @endif
        
        <!-- Update Status -->
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-edit"></i> Update Order Status
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.orders.update-status', $order->id) }}" method="POST" id="statusForm">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Order Status</label>
                        <select name="status" class="form-select" onchange="document.getElementById('statusForm').submit()">
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status == 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="declined" {{ $order->status == 'declined' ? 'selected' : '' }}>Declined</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </form>
                
                <div class="mt-3">
                    <button class="btn btn-primary w-100" onclick="document.getElementById('statusForm').submit()">
                        <i class="fas fa-save"></i> Update Status
                    </button>
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