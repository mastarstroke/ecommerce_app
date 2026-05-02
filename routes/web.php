<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{slug}', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/search', [ProductController::class, 'search'])->name('products.search');

Route::get('/cart/summary', [CartController::class, 'getCartSummary'])->name('cart.summary');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');
Route::put('/cart/update/{item}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{item}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/cart/count', [CartController::class, 'getCartCount'])->name('cart.count');

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // User Dashboard
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [UserDashboardController::class, 'editProfile'])->name('profile.edit');
    Route::put('/profile', [UserDashboardController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [UserDashboardController::class, 'updatePassword'])->name('profile.password');
    Route::put('/profile/avatar', [UserDashboardController::class, 'updateAvatar'])->name('profile.avatar');
    
    // Wishlist
    Route::post('/wishlist/{product}', [UserDashboardController::class, 'addToWishlist'])->name('wishlist.add');
    Route::delete('/wishlist/{product}', [UserDashboardController::class, 'removeFromWishlist'])->name('wishlist.remove');
    
    // Checkout & Orders
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::get('/checkout/success/{orderNumber}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{orderNumber}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    
    // Order Management
    Route::get('/orders/update-status/{id}/{status}', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::resource('orders', AdminOrderController::class);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::put('/orders/{id}/payment', [AdminOrderController::class, 'updatePaymentStatus'])->name('orders.update-payment');

    // Product Management
    Route::get('/products/toggle-status/{id}', [AdminProductController::class, 'toggleStatus'])->name('products.toggle-status');
    Route::get('/products/toggle-featured/{id}', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::post('/products/bulk-delete', [AdminProductController::class, 'bulkDelete'])->name('products.bulk-delete');
    Route::resource('products', AdminProductController::class);
    
    // Category Management
    Route::get('/categories/toggle-status/{id}', [AdminCategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
    Route::get('/categories/get-categories', [AdminCategoryController::class, 'getCategories'])->name('categories.get');
    Route::resource('categories', AdminCategoryController::class);
    
    // User Management
    Route::get('/users/toggle-admin/{id}', [AdminUserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::get('/users/verify-email/{id}', [AdminUserController::class, 'verifyEmail'])->name('users.verify-email');
    Route::get('/users/impersonate/{id}', [AdminUserController::class, 'impersonate'])->name('users.impersonate');
    Route::get('/users/stop-impersonate', [AdminUserController::class, 'stopImpersonate'])->name('users.stop-impersonate');
    Route::resource('users', AdminUserController::class);
    
});


// temporary debug cart route
Route::get('/debug-cart-status', function() {
    $cartService = app(\App\Services\CartService::class);
    $summary = $cartService->getCartSummary();
    
    // Get all carts in database for debugging
    $allCarts = \App\Models\Cart::with('items.product')->get();
    
    return response()->json([
        'is_logged_in' => auth()->check(),
        'user_id' => auth()->id(),
        'current_session' => session()->getId(),
        'cart_summary' => $summary,
        'all_carts_in_db' => $allCarts->map(function($cart) {
            return [
                'cart_id' => $cart->id,
                'user_id' => $cart->user_id,
                'session_id' => $cart->session_id,
                'item_count' => $cart->items->count(),
                'items' => $cart->items->map(function($item) {
                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'product_name' => $item->product ? $item->product->name : 'Deleted'
                    ];
                })
            ];
        })
    ]);
})->middleware('auth');