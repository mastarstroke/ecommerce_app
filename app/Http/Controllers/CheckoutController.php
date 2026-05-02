<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CheckoutService;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $checkoutService;
    protected $cartService;

    public function __construct(CheckoutService $checkoutService, CartService $cartService)
    {
        $this->middleware('auth');
        $this->checkoutService = $checkoutService;
        $this->cartService = $cartService;
    }

    /**
     * Show checkout page
     */
    public function index()
    {
        $cart = $this->cartService->getCartSummary();
        
        if (empty($cart['items']) || count($cart['items']) == 0) {
            return redirect()->route('cart.index')
                            ->with('error', 'Your cart is empty!');
        }
        
        $user = auth()->user();
        return view('checkout.index', compact('cart', 'user'));
    }

    /**
     * Process the checkout via AJAX
     */
    public function process(Request $request)
    {
        try {
            Log::info('Checkout process started', ['user_id' => auth()->id()]);
            
            // Validate request
            $validated = $request->validate([
                'shipping_name' => 'required|string|max:255',
                'shipping_address' => 'required|string|min:10',
                'shipping_city' => 'required|string|max:100',
                'shipping_postal' => 'required|string|max:20',
                'payment_method' => 'required|in:credit_card,paypal,cash_on_delivery',
                'notes' => 'nullable|string|max:500'
            ]);
            
            // Prepare order data
            $cart = $this->cartService->getCartSummary();
            
            if (empty($cart['items'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your cart is empty!'
                ], 422);
            }
            
            // Check if same as shipping is checked
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
            
            // Create order
            $order = $this->checkoutService->processCheckout(auth()->user(), $orderData, $cart['items']);
            
            Log::info('Checkout completed', [
                'user_id' => auth()->id(),
                'order_id' => $order->id,
                'order_number' => $order->order_number
            ]);
            
            // Return JSON response with redirect URL
            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully!',
                'order_number' => $order->order_number,
                'redirect_url' => route('checkout.success', $order->order_number)
            ]);
                            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Checkout failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process order: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Show order success page
     */
    public function success($orderNumber)
    {
        $order = \App\Models\Order::where('order_number', $orderNumber)
                                  ->with('items.product')
                                  ->firstOrFail();
        
        // Verify order belongs to user
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Clear cart session
        session()->forget('cart_count');
        
        return view('checkout.success', compact('order'));
    }
}