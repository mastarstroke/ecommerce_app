<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Modern E-Commerce')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        main {
            flex: 1;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .cart-icon {
            position: relative;
            cursor: pointer;
        }
        
        .cart-count-badge {
            position: absolute;
            top: -8px;
            right: -12px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 11px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
            animation: bounce 0.3s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.3); }
        }
        
        .mini-cart-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .mini-cart-item {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        
        .mini-cart-item:last-child {
            border-bottom: none;
        }
        
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .btn-add-to-cart {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            transition: all 0.3s;
        }
        
        .btn-add-to-cart:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-add-to-cart:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        
        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #fff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.6s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Footer Styles */
        .footer {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #ffffff;
            padding: 60px 0 20px;
            margin-top: 50px;
        }
        
        .footer a {
            color: #a8a8a8;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .footer a:hover {
            color: #667eea;
            transform: translateX(5px);
            display: inline-block;
        }
        
        .footer .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .footer .social-icons a:hover {
            background: #667eea;
            color: white;
            transform: translateY(-3px);
        }
        
        .footer .newsletter-input {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            color: white;
        }
        
        .footer .newsletter-input:focus {
            background: rgba(255,255,255,0.15);
            border-color: #667eea;
            box-shadow: none;
            color: white;
        }
        
        .footer .newsletter-input::placeholder {
            color: rgba(255,255,255,0.6);
        }
        
        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            padding-top: 20px;
            margin-top: 40px;
        }
        
        .payment-icons i {
            font-size: 32px;
            margin: 0 5px;
            color: rgba(255,255,255,0.6);
            transition: all 0.3s;
        }
        
        .payment-icons i:hover {
            color: #667eea;
            transform: scale(1.1);
        }
        
        .footer-links h5 {
            position: relative;
            display: inline-block;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        
        .footer-links h5:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 2px;
            background: #667eea;
        }
        
        .footer-links ul {
            list-style: none;
            padding: 0;
        }
        
        .footer-links ul li {
            margin-bottom: 10px;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}" style="font-size: 24px; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                ShopHub
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">Shop</a>
                    </li>
                </ul>
                
                <form class="d-flex me-3" action="{{ route('products.search') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="q" class="form-control" placeholder="Search products..." style="border-radius: 25px 0 0 25px;">
                        <button class="btn btn-outline-primary" type="submit" style="border-radius: 0 25px 25px 0;">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link cart-icon" href="#" id="cartDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-shopping-cart fa-lg"></i>
                            <span class="cart-count-badge" id="cart-count">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end mini-cart-dropdown" aria-labelledby="cartDropdown" id="mini-cart">
                            <li class="px-3 py-2 text-center text-muted">Loading cart...</li>
                        </ul>
                    </li>
                    
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle fa-lg"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('orders.index') }}">My Orders</a></li>
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile Settings</a></li>
                                @if(Auth::user()->is_admin)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="mb-3">
                        <h3 class="fw-bold" style="font-size: 28px; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                            ShopHub
                        </h3>
                    </div>
                    <p class="text-white-50 mb-3">Your one-stop destination for quality products at affordable prices. Shop with confidence and enjoy the best online shopping experience.</p>
                    <div class="social-icons">
                        <a href="#" class="d-inline-flex"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="d-inline-flex"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="d-inline-flex"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="d-inline-flex"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="d-inline-flex"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 footer-links">
                    <h5 class="fw-bold">Quick Links</h5>
                    <ul>
                        <li><a href="{{ route('home') }}"><i class="fas fa-chevron-right me-2"></i>Home</a></li>
                        <li><a href="{{ route('products.index') }}"><i class="fas fa-chevron-right me-2"></i>Shop</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>About Us</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Contact</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Blog</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 footer-links">
                    <h5 class="fw-bold">Customer Service</h5>
                    <ul>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>FAQs</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Shipping Info</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Returns Policy</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Privacy Policy</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right me-2"></i>Terms of Service</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold">Newsletter</h5>
                    <p class="text-white-50 mb-3">Subscribe to get exclusive offers and updates!</p>
                    <form class="mb-3" id="newsletterForm">
                        <div class="input-group">
                            <input type="email" class="form-control newsletter-input" placeholder="Your email address" required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-paper-plane"></i> Subscribe
                            </button>
                        </div>
                    </form>
                    <div class="payment-icons mt-3">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-amex"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <i class="fab fa-apple-pay"></i>
                        <i class="fab fa-google-pay"></i>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <p class="mb-0 text-white-50 small">
                            &copy; {{ date('Y') }} ShopHub. All rights reserved. | Crafted with <i class="fas fa-heart text-danger"></i> for you
                        </p>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <p class="mb-0 text-white-50 small">
                            <i class="fas fa-shield-alt"></i> Secure Shopping | <i class="fas fa-truck"></i> Fast Delivery
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <div class="toast-container"></div>
    
    <!-- Back to Top Button -->
    <button onclick="scrollToTop()" id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4" style="display: none; width: 45px; height: 45px; z-index: 99;">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Global cart functions
        let cartUpdateInProgress = false;
        
        // Initialize cart on page load
        $(document).ready(function() {
            loadCartCount();
            loadMiniCart();
            
            // Refresh cart every 30 seconds
            setInterval(function() {
                loadCartCount();
                if ($('#cartDropdown').hasClass('show')) {
                    loadMiniCart();
                }
            }, 30000);
            
            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('#backToTop').fadeIn();
                } else {
                    $('#backToTop').fadeOut();
                }
            });
            
            // Newsletter subscription
            $('#newsletterForm').submit(function(e) {
                e.preventDefault();
                const email = $(this).find('input[type="email"]').val();
                Swal.fire({
                    icon: 'success',
                    title: 'Subscribed!',
                    text: `Thank you for subscribing with ${email}`,
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                $(this)[0].reset();
            });
        });
        
        // Load cart count only
        function loadCartCount() {
            $.ajax({
                url: '{{ route("cart.count") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        updateCartCountBadge(response.count);
                    }
                },
                error: function() {
                    console.log('Failed to load cart count');
                }
            });
        }
        
        // Load mini cart preview
        function loadMiniCart() {
            $.ajax({
                url: '{{ route("cart.summary") }}',
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        updateMiniCartDropdown(response);
                        updateCartCountBadge(response.total_quantity);
                    } else {
                        $('#mini-cart').html('<li class="px-3 py-4 text-center text-muted">Unable to load cart</li>');
                    }
                },
                error: function() {
                    $('#mini-cart').html('<li class="px-3 py-4 text-center text-muted">Error loading cart</li>');
                }
            });
        }
        
        // Update cart count badge
        function updateCartCountBadge(count) {
            const badge = $('#cart-count');
            const oldCount = parseInt(badge.text());
            
            badge.text(count);
            
            if (count > oldCount && oldCount > 0) {
                badge.css('animation', 'none');
                setTimeout(() => {
                    badge.css('animation', 'bounce 0.3s ease');
                }, 10);
            }
            
            // Hide badge if count is 0
            if (count === 0) {
                badge.hide();
            } else {
                badge.show();
            }
        }
        
        // Update mini cart dropdown
        function updateMiniCartDropdown(cart) {
            let html = '';
            
            if (cart.items && cart.items.length > 0) {
                cart.items.forEach(item => {
                    html += `
                        <li class="mini-cart-item px-3">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    ${item.image ? 
                                        `<img src="/storage/${item.image}" style="width: 50px; height: 50px; object-fit: cover;" class="rounded">` : 
                                        `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                            <i class="fas fa-image text-muted"></i>
                                        </div>`
                                    }
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="fw-bold small">${item.name.substring(0, 30)}${item.name.length > 30 ? '...' : ''}</div>
                                    <div class="small text-muted">Qty: ${item.quantity}</div>
                                    <div class="text-primary fw-bold small">$${item.total.toFixed(2)}</div>
                                </div>
                                <button onclick="removeCartItem(${item.id})" class="btn btn-sm btn-link text-danger p-0">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </li>
                    `;
                });
                
                html += `
                    <li class="px-3 py-2 border-top">
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>$${cart.total.toFixed(2)}</span>
                        </div>
                        <div class="d-grid gap-2 mt-2">
                            <a href="{{ route('cart.index') }}" class="btn btn-primary btn-sm">View Cart</a>
                            <a href="{{ route('checkout.index') }}" class="btn btn-success btn-sm">Checkout</a>
                        </div>
                    </li>
                `;
            } else {
                html = '<li class="px-3 py-4 text-center text-muted">Your cart is empty</li>';
            }
            
            $('#mini-cart').html(html);
        }
        
        // Add to cart function
        function addToCart(productId, quantity = 1, button = null) {
            if (cartUpdateInProgress) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait',
                    text: 'Previous request is still processing...',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                return;
            }
            
            const $button = $(button);
            const originalHtml = $button.html();
            
            // Show loading state
            $button.html('<span class="loading-spinner"></span> Adding...').prop('disabled', true);
            cartUpdateInProgress = true;
            
            $.ajax({
                url: `/cart/add/${productId}`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        // Update cart displays
                        loadCartCount();
                        loadMiniCart();
                        
                        // Show success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            html: `<strong>${response.item_added.name}</strong><br>
                                   Quantity: ${response.item_added.quantity}<br>
                                   Total: $${response.item_added.total.toFixed(2)}`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                },
                // error: function(xhr) {
                //     let message = 'Failed to add to cart';
                //     if (xhr.responseJSON && xhr.responseJSON.message) {
                //         message = xhr.responseJSON.message;
                //     }
                    
                //     Swal.fire({
                //         icon: 'error',
                //         title: 'Error',
                //         text: message,
                //         toast: true,
                //         position: 'top-end',
                //         showConfirmButton: false,
                //         timer: 3000
                //     });
                // },
                error: function(xhr) {
                    console.log('AJAX Error:', xhr);
                    let message = 'Failed to add to cart';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.status === 419) {
                        message = 'Session expired. Please refresh the page.';
                    } else if (xhr.status === 500) {
                        message = 'Server error. Please try again.';
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                },
                complete: function() {
                    $button.html(originalHtml).prop('disabled', false);
                    cartUpdateInProgress = false;
                }
            });
        }
        
        // Remove cart item
        function removeCartItem(itemId) {
            Swal.fire({
                title: 'Remove item?',
                text: "This item will be removed from your cart",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/cart/remove/${itemId}`,
                        method: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                loadCartCount();
                                loadMiniCart();
                                
                                // If on cart page, reload the page content
                                if (window.location.pathname === '/cart') {
                                    location.reload();
                                }
                                
                                Swal.fire('Removed!', 'Item removed from cart.', 'success');
                            }
                        },
                        error: function() {
                            Swal.fire('Error!', 'Failed to remove item.', 'error');
                        }
                    });
                }
            });
        }
        
        // Update cart item quantity (for cart page)
        function updateCartItemQuantity(itemId, quantity) {
            $.ajax({
                url: `/cart/update/${itemId}`,
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        loadCartCount();
                        loadMiniCart();
                        
                        // Update cart page if on cart page
                        if (window.location.pathname === '/cart') {
                            updateCartDisplay(response);
                        }
                    }
                }
            });
        }
        
        // Update cart page display
        function updateCartDisplay(data) {
            // Update totals
            $('#cart-subtotal').text('$' + data.cart_subtotal.toFixed(2));
            $('#cart-tax').text('$' + data.cart_tax.toFixed(2));
            $('#cart-shipping').text('$' + data.cart_shipping.toFixed(2));
            $('#cart-total').text('$' + data.cart_total.toFixed(2));
            
            // Update each item total
            if (data.items) {
                data.items.forEach(item => {
                    $(`#item-total-${item.id}`).text('$' + item.total.toFixed(2));
                });
            }
        }
        
        // Scroll to top function
        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>