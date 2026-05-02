<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCartSummary();
        
        // Debug - log cart contents
        Log::info('Cart page loaded', [
            'user_id' => auth()->id(),
            'cart_items' => count($cart['items']),
            'cart_total' => $cart['total']
        ]);
        
        return view('cart.index', compact('cart'));
    }

    
    public function add(Request $request, Product $product)
    {
        try {
            Log::info('Add to cart request started', [
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
            ]);
            
            $request->validate([
                'quantity' => 'integer|min:1|max:' . $product->stock_quantity
            ]);

            $quantity = $request->get('quantity', 1);
            
            // Add to cart
            $cart = $this->cartService->addItem($product, $quantity, $request->get('attributes', []));
            
            // Get updated cart count
            $cartCount = $this->cartService->getCartCount();
            
            Log::info('Add to cart successful', [
                'cart_count' => $cartCount,
                'cart_total' => $cart['total'],
                'items_in_cart' => count($cart['items'])
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Product added to cart successfully!',
                'cart_count' => $cartCount,
                'cart_total' => $cart['total'],
                'cart_subtotal' => $cart['subtotal'],
                'cart_items' => $cart['items'],
                'item_added' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'quantity' => $quantity,
                    'price' => $product->price,
                    'total' => $product->price * $quantity
                ]
            ]);
                            
        } catch (\Exception $e) {
            Log::error('Cart add error: ' . $e->getMessage(), [
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }


    /**
     * Update cart item quantity via AJAX
     */
    public function update(Request $request, $itemId)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:0'
            ]);

            $cart = $this->cartService->updateQuantity($itemId, $request->quantity);
            $cartCount = $this->cartService->getCartCount();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart updated successfully!',
                    'cart_count' => $cartCount,
                    'cart_total' => $cart['total'],
                    'cart_subtotal' => $cart['subtotal'],
                    'cart_tax' => $cart['tax'],
                    'cart_shipping' => $cart['shipping'],
                    'items' => $cart['items']
                ]);
            }

            return redirect()->route('cart.index')
                            ->with('success', 'Cart updated successfully!');
                            
        } catch (\Exception $e) {
            Log::error('Cart update error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                            ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove item from cart via AJAX
     */
    public function remove(Request $request, $itemId)
    {
        try {
            $cart = $this->cartService->removeItem($itemId);
            $cartCount = $this->cartService->getCartCount();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Item removed from cart!',
                    'cart_count' => $cartCount,
                    'cart_total' => $cart['total'],
                    'cart_subtotal' => $cart['subtotal'],
                    'items' => $cart['items']
                ]);
            }

            return redirect()->route('cart.index')
                            ->with('success', 'Item removed from cart!');
                            
        } catch (\Exception $e) {
            Log::error('Cart remove error: ' . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()
                            ->with('error', $e->getMessage());
        }
    }

    /**
     * Get cart count via AJAX
     */
    public function getCartCount(Request $request)
    {
        $count = $this->cartService->getCartCount();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        }
        
        return $count;
    }

    /**
     * Get full cart summary via AJAX
     */
    public function getCartSummary(Request $request)
    {
        $cart = $this->cartService->getCartSummary();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($cart);
        }
        
        return $cart;
    }
}