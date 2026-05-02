@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<div class="container py-5">
    <h1 class="display-5 fw-bold mb-4">Checkout</h1>
    
    @if($cart && count($cart['items']) > 0)
        <div class="row">
            <div class="col-lg-7">
                <!-- Checkout Form -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form id="checkoutForm">
                            @csrf
                            
                            <h5 class="fw-bold mb-3">Contact Information</h5>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" value="{{ Auth::user()->email }}" readonly>
                            </div>
                            
                            <h5 class="fw-bold mb-3 mt-4">Shipping Address</h5>
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" name="shipping_name" id="shipping_name" class="form-control" 
                                       value="{{ old('shipping_name', Auth::user()->name) }}" required>
                                <div class="invalid-feedback" id="shipping_name_error"></div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Street Address *</label>
                                <textarea name="shipping_address" id="shipping_address" class="form-control" 
                                          rows="2" required>{{ old('shipping_address', Auth::user()->address) }}</textarea>
                                <div class="invalid-feedback" id="shipping_address_error"></div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">City *</label>
                                    <input type="text" name="shipping_city" id="shipping_city" class="form-control" 
                                           value="{{ old('shipping_city') }}" required>
                                    <div class="invalid-feedback" id="shipping_city_error"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Postal Code *</label>
                                    <input type="text" name="shipping_postal" id="shipping_postal" class="form-control" 
                                           value="{{ old('shipping_postal') }}" required>
                                    <div class="invalid-feedback" id="shipping_postal_error"></div>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-3 mt-4">Billing Address</h5>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="same_as_shipping" checked>
                                <label class="form-check-label" for="same_as_shipping">
                                    Same as shipping address
                                </label>
                            </div>
                            
                            <div id="billing_address_section" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="billing_name" id="billing_name" class="form-control" 
                                           value="{{ old('billing_name', Auth::user()->name) }}">
                                    <div class="invalid-feedback" id="billing_name_error"></div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Street Address *</label>
                                    <textarea name="billing_address" id="billing_address" class="form-control" rows="2">{{ old('billing_address', Auth::user()->address) }}</textarea>
                                    <div class="invalid-feedback" id="billing_address_error"></div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">City *</label>
                                        <input type="text" name="billing_city" id="billing_city" class="form-control" value="{{ old('billing_city') }}">
                                        <div class="invalid-feedback" id="billing_city_error"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Postal Code *</label>
                                        <input type="text" name="billing_postal" id="billing_postal" class="form-control" value="{{ old('billing_postal') }}">
                                        <div class="invalid-feedback" id="billing_postal_error"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold mb-3 mt-4">Payment Method</h5>
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                    <label class="form-check-label" for="credit_card">
                                        <i class="fab fa-cc-visa"></i> <i class="fab fa-cc-mastercard"></i> Credit Card
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal"></i> PayPal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cash_on_delivery">
                                    <label class="form-check-label" for="cash_on_delivery">
                                        <i class="fas fa-money-bill"></i> Cash on Delivery
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Credit Card Details (shown when credit card selected) -->
                            <div id="credit_card_details" class="mt-3">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Card Number</label>
                                        <input type="text" class="form-control" placeholder="1234 5678 9012 3456" id="card_number">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" placeholder="MM/YY" id="expiry_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">CVV</label>
                                        <input type="text" class="form-control" placeholder="123" id="cvv">
                                    </div>
                                </div>
                                <small class="text-muted">Demo mode: No actual charge will be made</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Order Notes (Optional)</label>
                                <textarea name="notes" id="notes" class="form-control" rows="3" 
                                          placeholder="Special instructions for delivery...">{{ old('notes') }}</textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-5">
                <!-- Order Summary -->
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Your Order</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($cart['items'] as $item)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="fw-bold">{{ $item['quantity'] }}x</span>
                                        <span class="ms-2">{{ $item['name'] }}</span>
                                        @if($item['attributes'])
                                            <br>
                                            <small class="text-muted ms-4">
                                                @foreach($item['attributes'] as $key => $value)
                                                    {{ ucfirst($key) }}: {{ $value }}
                                                @endforeach
                                            </small>
                                        @endif
                                    </div>
                                    <span class="fw-bold">${{ number_format($item['total'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>${{ number_format($cart['subtotal'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tax (10%):</span>
                                <span>${{ number_format($cart['tax'], 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping:</span>
                                <span>${{ number_format($cart['shipping'], 2) }}</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong class="fs-5">Total:</strong>
                                <strong class="fs-5 text-primary">${{ number_format($cart['total'], 2) }}</strong>
                            </div>
                            
                            <div class="alert alert-info small">
                                <i class="fas fa-truck"></i> Free shipping on orders over $100
                            </div>
                            
                            <button type="button" class="btn btn-primary btn-lg w-100" id="placeOrderBtn">
                                <i class="fas fa-check-circle"></i> Place Order
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Security Badges -->
                <div class="card shadow-sm mt-3">
                    <div class="card-body text-center">
                        <i class="fas fa-lock fa-2x text-success mb-2"></i>
                        <p class="small text-muted mb-0">
                            Your payment information is encrypted and secure.
                        </p>
                        <div class="mt-2">
                            <i class="fab fa-cc-visa fa-2x mx-1"></i>
                            <i class="fab fa-cc-mastercard fa-2x mx-1"></i>
                            <i class="fab fa-cc-amex fa-2x mx-1"></i>
                            <i class="fab fa-cc-paypal fa-2x mx-1"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
            <h3>Your cart is empty</h3>
            <p class="text-muted">Please add items to your cart before checking out.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary">Continue Shopping</a>
        </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle billing address section
    $('#same_as_shipping').change(function() {
        if ($(this).is(':checked')) {
            $('#billing_address_section').slideUp();
            // Remove required attributes
            $('#billing_name').removeAttr('required');
            $('#billing_address').removeAttr('required');
            $('#billing_city').removeAttr('required');
            $('#billing_postal').removeAttr('required');
        } else {
            $('#billing_address_section').slideDown();
            // Add required attributes
            $('#billing_name').attr('required', true);
            $('#billing_address').attr('required', true);
            $('#billing_city').attr('required', true);
            $('#billing_postal').attr('required', true);
        }
    });
    
    // Toggle credit card details
    $('input[name="payment_method"]').change(function() {
        if ($(this).val() === 'credit_card') {
            $('#credit_card_details').slideDown();
        } else {
            $('#credit_card_details').slideUp();
        }
    });
    
    // Clear validation errors on input
    $('input, textarea').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').hide();
    });
    
    // Place Order with AJAX
    $('#placeOrderBtn').click(function(e) {
        e.preventDefault();
        
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        // Validate form
        let isValid = true;
        
        // Required fields validation
        const requiredFields = ['shipping_name', 'shipping_address', 'shipping_city', 'shipping_postal'];
        
        requiredFields.forEach(field => {
            const input = $(`#${field}`);
            if (!input.val()) {
                input.addClass('is-invalid');
                $(`#${field}_error`).text('This field is required').show();
                isValid = false;
            }
        });
        
        // Validate billing if not same as shipping
        if (!$('#same_as_shipping').is(':checked')) {
            const billingFields = ['billing_name', 'billing_address', 'billing_city', 'billing_postal'];
            billingFields.forEach(field => {
                const input = $(`#${field}`);
                if (!input.val()) {
                    input.addClass('is-invalid');
                    $(`#${field}_error`).text('This field is required').show();
                    isValid = false;
                }
            });
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Missing Information',
                text: 'Please fill in all required fields.',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
            return;
        }
        
        // Show loading state
        $btn.html('<span class="loading-spinner"></span> Processing Order...').prop('disabled', true);
        
        // Gather form data
        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            shipping_name: $('#shipping_name').val(),
            shipping_address: $('#shipping_address').val(),
            shipping_city: $('#shipping_city').val(),
            shipping_postal: $('#shipping_postal').val(),
            payment_method: $('input[name="payment_method"]:checked').val(),
            notes: $('#notes').val(),
            same_as_shipping: $('#same_as_shipping').is(':checked')
        };
        
        // Add billing data if different
        if (!formData.same_as_shipping) {
            formData.billing_name = $('#billing_name').val();
            formData.billing_address = $('#billing_address').val();
            formData.billing_city = $('#billing_city').val();
            formData.billing_postal = $('#billing_postal').val();
        }
        
        // Make AJAX request
        $.ajax({
            url: '{{ route("checkout.process") }}',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Placed!',
                        html: `Your order #${response.order_number} has been placed successfully!<br>Redirecting to order details...`,
                        showConfirmButton: false,
                        timer: 2000
                    });
                    
                    // Redirect to success page after 2 seconds
                    setTimeout(function() {
                        window.location.href = response.redirect_url;
                    }, 2000);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to place order. Please try again.';
                
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    // Display validation errors
                    const errors = xhr.responseJSON.errors;
                    errorMessage = '';
                    
                    $.each(errors, function(field, messages) {
                        $(`#${field}`).addClass('is-invalid');
                        $(`#${field}_error`).text(messages[0]).show();
                        errorMessage += messages[0] + '\n';
                    });
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please check the form for errors.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                    Swal.fire({
                        icon: 'error',
                        title: 'Order Failed',
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: errorMessage,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
                
                console.error('Checkout error:', xhr);
            },
            complete: function() {
                $btn.html(originalHtml).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush
@endsection