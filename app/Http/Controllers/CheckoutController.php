<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\LogsActivity;

class CheckoutController extends Controller
{
    use LogsActivity;

    protected $checkoutService;
    protected $cartService;

    public function __construct(CheckoutService $checkoutService, CartService $cartService)
    {
        $this->middleware('auth');
        $this->checkoutService = $checkoutService;
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCartSummary();
        
        if (empty($cart['items']) || count($cart['items']) == 0) {
            $this->logActivity(
                'viewed',
                'checkout',
                'Checkout page accessed with empty cart',
                null,
                null,
                ['user_id' => auth()->id()],
                'failed'
            );
            
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty!');
        }
        
        $user = auth()->user();
        
        $this->logActivity(
            'viewed',
            'checkout',
            'Checkout page accessed',
            null,
            null,
            [
                'user_id' => auth()->id(),
                'cart_items' => count($cart['items']),
                'cart_total' => $cart['total'],
                'payment_methods_available' => ['credit_card', 'paypal', 'cash_on_delivery']
            ],
            'success'
        );
        
        return view('checkout.index', compact('cart', 'user'));
    }

    public function process(Request $request)
    {
        try {
            $this->logActivity(
                'processed',
                'checkout',
                'Checkout process started',
                null,
                null,
                [
                    'user_id' => auth()->id(),
                    'session_id' => session()->getId(),
                    'request_data' => $request->except(['_token'])
                ],
                'success'
            );
            
            $validated = $request->validate([
                'shipping_name' => 'required|string|max:255',
                'shipping_address' => 'required|string|min:10',
                'shipping_city' => 'required|string|max:100',
                'shipping_postal' => 'required|string|max:20',
                'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
                'notes' => 'nullable|string|max:500'
            ]);
            
            $cart = $this->cartService->getCartSummary();
            
            if (empty($cart['items'])) {
                $this->logActivity(
                    'failed',
                    'checkout',
                    'Checkout failed - cart is empty',
                    null,
                    null,
                    ['user_id' => auth()->id()],
                    'failed'
                );
                
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty!'
                ], 422);
            }
            
            $sameAsShipping = $request->boolean('same_as_shipping', true);
            
            if ($sameAsShipping) {
                $billing_name = $validated['shipping_name'];
                $billing_address = $validated['shipping_address'];
                $billing_city = $validated['shipping_city'];
                $billing_postal = $validated['shipping_postal'];
            } else {
                $request->validate([
                    'billing_name' => 'required|string|max:255',
                    'billing_address' => 'required|string|min:10',
                    'billing_city' => 'required|string|max:100',
                    'billing_postal' => 'required|string|max:20',
                ]);
                
                $billing_name = $request->billing_name;
                $billing_address = $request->billing_address;
                $billing_city = $request->billing_city;
                $billing_postal = $request->billing_postal;
            }
            
            $orderData = [
                'user_id' => auth()->id(),
                'shipping_name' => $validated['shipping_name'],
                'shipping_address' => $validated['shipping_address'],
                'shipping_city' => $validated['shipping_city'],
                'shipping_postal' => $validated['shipping_postal'],
                'billing_name' => $billing_name,
                'billing_address' => $billing_address,
                'billing_city' => $billing_city,
                'billing_postal' => $billing_postal,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'subtotal' => $cart['subtotal'],
                'tax' => $cart['tax'],
                'shipping_cost' => $cart['shipping'],
                'total' => $cart['total']
            ];
            
            $order = $this->checkoutService->processCheckout(auth()->user(), $orderData, $cart['items']);
            
            $this->logActivity(
                'created',
                'order',
                "Order #{$order->order_number} was placed successfully",
                null,
                $order->toArray(),
                [
                    'user_id' => auth()->id(),
                    'user_name' => auth()->user()->name,
                    'user_email' => auth()->user()->email,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'subtotal' => $order->subtotal,
                    'tax' => $order->tax,
                    'shipping_cost' => $order->shipping_cost,
                    'total' => $order->total,
                    'payment_method' => $order->payment_method,
                    'cart_items_count' => count($cart['items']),
                    'shipping_address' => $orderData['shipping_address'],
                    'billing_address' => $orderData['billing_address']
                ],
                'success'
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_number' => $order->order_number,
                'redirect_url' => route('checkout.success', $order->order_number)
            ]);
                            
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logActivity(
                'failed',
                'checkout',
                'Checkout validation failed',
                $request->all(),
                null,
                [
                    'user_id' => auth()->id(),
                    'validation_errors' => $e->errors()
                ],
                'failed'
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'checkout',
                'Checkout process error: ' . $e->getMessage(),
                [
                    'user_id' => auth()->id(),
                    'request_data' => $request->except(['_token'])
                ],
                null,
                [
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'error_file' => $e->getFile(),
                    'error_line' => $e->getLine()
                ],
                'failed'
            );
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function success($orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)
                                  ->with('items.product')
                                  ->firstOrFail();
        
        if ($order->user_id !== auth()->id()) {
            $this->logActivity(
                'failed',
                'checkout',
                'Unauthorized access to order success page',
                null,
                null,
                [
                    'user_id' => auth()->id(),
                    'order_number' => $orderNumber,
                    'order_owner_id' => $order->user_id
                ],
                'failed'
            );
            
            abort(403);
        }
        
        session()->forget('cart_count');
        
        $this->logActivity(
            'viewed',
            'checkout',
            "Order success page viewed for order #{$orderNumber}",
            null,
            $order->toArray(),
            [
                'user_id' => auth()->id(),
                'order_number' => $orderNumber,
                'order_total' => $order->total,
                'payment_method' => $order->payment_method
            ],
            'success'
        );
        
        return view('checkout.success', compact('order'));
    }
}