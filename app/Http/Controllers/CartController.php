<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\LogsActivity;

class CartController extends Controller
{
    use LogsActivity;

    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index()
    {
        $cart = $this->cartService->getCartSummary();
        
        // Log cart page view
        $this->logActivity(
            'viewed',
            'cart',
            'User viewed cart page',
            null,
            null,
            [
                'user_id' => auth()->id(),
                'cart_items' => count($cart['items']),
                'cart_total' => $cart['total']
            ],
            'success'
        );
        
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
            $oldCartData = $this->cartService->getCartSummary();
            
            // Add to cart
            $cart = $this->cartService->addItem($product, $quantity, $request->get('attributes', []));
            
            // Get updated cart count
            $cartCount = $this->cartService->getCartCount();

            // Log cart addition with details
            $this->logActivity(
                'created',
                'cart',
                "Product '{$product->name}' (x{$quantity}) added to cart",
                ['cart_before' => $oldCartData],
                ['cart_after' => $cart],
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'total_price' => $product->price * $quantity,
                    'new_cart_total' => $cart['total'],
                    'new_cart_items' => $cart['total_quantity']
                ],
                'success'
            );
            
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
            // Log error
            $this->logActivity(
                'failed',
                'cart',
                "Failed to add product to cart: {$e->getMessage()}",
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'requested_quantity' => $request->get('quantity', 1),
                    'available_stock' => $product->stock_quantity
                ],
                null,
                [
                    'product_id' => $product->id,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ],
                'failed'
            );
            
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


    public function update(Request $request, $itemId)
    {
        try {
            $request->validate([
                'quantity' => 'required|integer|min:0'
            ]);

            // Get old cart data before update
            $oldCartData = $this->cartService->getCartSummary();
            $oldItemData = null;
            foreach ($oldCartData['items'] as $item) {
                if ($item['id'] == $itemId) {
                    $oldItemData = $item;
                    break;
                }
            }

            $cart = $this->cartService->updateQuantity($itemId, $request->quantity);
            $cartCount = $this->cartService->getCartCount();

            // Log cart update
            $this->logActivity(
                'updated',
                'cart',
                "Cart item quantity updated from {$oldItemData['quantity']} to {$request->quantity}",
                ['old_cart' => $oldCartData],
                ['new_cart' => $cart],
                [
                    'item_id' => $itemId,
                    'product_name' => $oldItemData['name'] ?? 'Unknown',
                    'old_quantity' => $oldItemData['quantity'] ?? 0,
                    'new_quantity' => $request->quantity,
                    'old_total' => $oldCartData['total'],
                    'new_total' => $cart['total']
                ],
                'success'
            );

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

            return redirect()->route('cart.index')->with('success', 'Cart updated successfully!');
                            
        } catch (\Exception $e) {
            // Log error
            $this->logActivity(
                'failed',
                'cart',
                "Failed to update cart: {$e->getMessage()}",
                ['item_id' => $itemId, 'requested_quantity' => $request->quantity ?? null],
                null,
                [
                    'item_id' => $itemId,
                    'quantity' => $request->quantity ?? null,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function remove(Request $request, $itemId)
    {
        try {
            // Get cart data before removal
            $oldCartData = $this->cartService->getCartSummary();
            $removedItem = null;
            foreach ($oldCartData['items'] as $item) {
                if ($item['id'] == $itemId) {
                    $removedItem = $item;
                    break;
                }
            }

            $cart = $this->cartService->removeItem($itemId);
            $cartCount = $this->cartService->getCartCount();

            // Log cart removal
            if ($removedItem) {
                $this->logActivity(
                    'deleted',
                    'cart',
                    "Product '{$removedItem['name']}' removed from cart",
                    ['cart_before' => $oldCartData],
                    ['cart_after' => $cart],
                    [
                        'item_id' => $itemId,
                        'product_id' => $removedItem['product_id'],
                        'product_name' => $removedItem['name'],
                        'quantity_removed' => $removedItem['quantity'],
                        'value_removed' => $removedItem['total'],
                        'new_cart_total' => $cart['total']
                    ],
                    'success'
                );
            } else {
                $this->logActivity(
                    'deleted',
                    'cart',
                    "Item removed from cart (item not found in cart data)",
                    null,
                    null,
                    ['item_id' => $itemId],
                    'success'
                );
            }

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

            return redirect()->route('cart.index')->with('success', 'Item removed from cart!');
                            
        } catch (\Exception $e) {
            // Log error
            $this->logActivity(
                'failed',
                'cart',
                "Failed to remove item from cart: {$e->getMessage()}",
                ['item_id' => $itemId],
                null,
                [
                    'item_id' => $itemId,
                    'user_id' => auth()->id(),
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


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


    public function getCartSummary(Request $request)
    {
        $cart = $this->cartService->getCartSummary();
        
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($cart);
        }
        
        return $cart;
    }


    public function clear(Request $request)
    {
        try {
            $oldCartData = $this->cartService->getCartSummary();
            
            $cart = $this->cartService->clearCart();
            $cartCount = $this->cartService->getCartCount();

            // Log cart clear
            $this->logActivity(
                'deleted',
                'cart',
                "Cart cleared - removed {$oldCartData['total_quantity']} items worth \${$oldCartData['total']}",
                ['cart_before' => $oldCartData],
                ['cart_after' => $cart],
                [
                    'items_removed' => $oldCartData['total_quantity'],
                    'value_removed' => $oldCartData['total'],
                    'user_id' => auth()->id()
                ],
                'success'
            );

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart cleared successfully!',
                    'cart_count' => $cartCount
                ]);
            }

            return redirect()->route('cart.index')->with('success', 'Cart cleared successfully!');
                            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'cart',
                "Failed to clear cart: {$e->getMessage()}",
                null,
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 422);
            }
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}