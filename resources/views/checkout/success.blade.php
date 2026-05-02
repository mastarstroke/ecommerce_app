@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-success">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 80px; height: 80px;">
                            <i class="fas fa-check fa-3x text-white"></i>
                        </div>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Order Placed Successfully!</h2>
                    <p class="text-muted mb-4">Thank you for your purchase. Your order has been received.</p>
                    
                    <div class="alert alert-info">
                        <strong>Order Number:</strong> #{{ $order->order_number }}
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                                <h6>Order Confirmation</h6>
                                <small class="text-muted">Sent to your email</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                                <h6>Estimated Delivery</h6>
                                <small class="text-muted">3-5 business days</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="border rounded p-3">
                                <i class="fas fa-headset fa-2x text-primary mb-2"></i>
                                <h6>Customer Support</h6>
                                <small class="text-muted">24/7 available</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('orders.show', $order->order_number) }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Order Details
                        </a>
                        <a href="{{ route('products.index') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-shopping-cart"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection