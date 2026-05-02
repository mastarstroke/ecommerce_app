{{-- resources/views/cart/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Shopping Cart')

@section('content')
<div class="container py-5">
    <h1 class="display-5 fw-bold mb-4">Shopping Cart</h1>
    
    <div id="cart-content">
        @if($cart['success'] && count($cart['items']) > 0)
            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Total</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cart-items">
                                        @foreach($cart['items'] as $item)
                                        <tr id="cart-item-{{ $item['id'] }}">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item['image'])
                                                        <img src="{{ asset('storage/' . $item['image']) }}" 
                                                             class="rounded me-3" 
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h6 class="mb-0">{{ $item['name'] }}</h6>
                                                        <small class="text-muted">SKU: {{ $item['sku'] }}</small>
                                                        @if($item['attributes'])
                                                            <br>
                                                            <small class="text-muted">
                                                                @foreach($item['attributes'] as $key => $value)
                                                                    {{ ucfirst($key) }}: {{ $value }}
                                                                @endforeach
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="fw-bold">${{ number_format($item['price'], 2) }}</td>
                                            <td>
                                                <input type="number" 
                                                       class="form-control cart-quantity" 
                                                       value="{{ $item['quantity'] }}" 
                                                       min="1" 
                                                       max="{{ $item['stock'] }}"
                                                       data-item-id="{{ $item['id'] }}"
                                                       style="width: 80px;">
                                            </td>
                                            <td class="fw-bold" id="item-total-{{ $item['id'] }}">
                                                ${{ number_format($item['total'], 2) }}
                                            </td>
                                            <td>
                                                <button onclick="removeCartItem({{ $item['id'] }})" class="btn btn-link text-danger">
                                                    <i class="fas fa-trash fa-lg"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-4">Order Summary</h5>
                            
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span id="cart-subtotal">${{ number_format($cart['subtotal'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span id="cart-tax">${{ number_format($cart['tax'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span id="cart-shipping">${{ number_format($cart['shipping'], 2) }}</span>
                            </div>
                            
                            <hr>
                            
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total:</strong>
                                <strong class="fs-4 text-primary" id="cart-total">${{ number_format($cart['total'], 2) }}</strong>
                            </div>
                            
                            <div class="alert alert-info small">
                                <i class="fas fa-truck"></i> Free shipping on orders over $100
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="{{ route('checkout.index') }}" class="btn btn-primary btn-lg">
                                    Proceed to Checkout
                                </a>
                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
                                    Continue Shopping
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted">Looks like you haven't added anything to your cart yet.</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary">Start Shopping</a>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle quantity change on cart page
        $('.cart-quantity').on('change', function() {
            const itemId = $(this).data('item-id');
            const quantity = $(this).val();
            updateCartItemQuantity(itemId, quantity);
        });
        
        // Debounce quantity updates
        let timeout;
        $('.cart-quantity').on('input', function() {
            clearTimeout(timeout);
            const $this = $(this);
            timeout = setTimeout(function() {
                $this.trigger('change');
            }, 500);
        });
    });
</script>
@endpush