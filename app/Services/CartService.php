<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class CartService
{
    protected $cart = null;
    protected $initialized = false;

    public function __construct()
    {
        // Cart will be initialized lazily when needed
    }


    protected function initializeCart(): void
    {
        if ($this->initialized) {
            return;
        }

        try {
            if (Auth::check()) {
                $this->cart = Cart::where('user_id', Auth::id())->first();
                
                // FOR USER - Get existing cart or create ONE
                if (!$this->cart) {
                    $this->cart = Cart::create([
                        'user_id' => Auth::id(),
                        'session_id' => null
                    ]);
                    Log::info('Created new cart for user', ['user_id' => Auth::id(), 'cart_id' => $this->cart->id]);
                } else {
                    Log::info('Found existing cart for user', ['user_id' => Auth::id(), 'cart_id' => $this->cart->id]);
                }
                
                // Delete any orphaned session carts for this user
                Cart::where('session_id', '!=', null)
                    ->whereNull('user_id')
                    ->delete();
                    
            } else {
                // FOR GUESTS - Get existing session cart or create ONE
                $sessionId = Session::getId();
                $this->cart = Cart::where('session_id', $sessionId)->first();
                
                if (!$this->cart) {
                    // Create only ONE cart for this session
                    $this->cart = Cart::create([
                        'user_id' => null,
                        'session_id' => $sessionId
                    ]);
                    Log::info('Created new cart for session', ['session_id' => $sessionId, 'cart_id' => $this->cart->id]);
                } else {
                    Log::info('Found existing cart for session', ['session_id' => $sessionId, 'cart_id' => $this->cart->id]);
                }
            }
            
            $this->initialized = true;
            $this->cart->load('items.product');
            
        } catch (\Exception $e) {
            Log::error('Cart initialization failed: ' . $e->getMessage());
            throw $e;
        }
    }


    public function addItem(Product $product, int $quantity = 1, array $attributes = []): array
    {
        $this->initializeCart();
        
        try {
            // Check stock availability
            if ($quantity > $product->stock_quantity) {
                throw new \Exception("Only {$product->stock_quantity} items available in stock.");
            }
            
            // Find existing cart item
            $cartItem = CartItem::where('cart_id', $this->cart->id)
                               ->where('product_id', $product->id)
                               ->first();

            if ($cartItem) {
                $newQuantity = $cartItem->quantity + $quantity;
                
                if ($newQuantity > $product->stock_quantity) {
                    throw new \Exception("You already have {$cartItem->quantity} in cart. Only " . 
                                        ($product->stock_quantity - $cartItem->quantity) . " more available.");
                }
                
                $cartItem->update([
                    'quantity' => $newQuantity,
                    'attributes' => !empty($attributes) ? json_encode($attributes) : null
                ]);
                
                Log::info('Updated cart item', ['cart_item_id' => $cartItem->id, 'quantity' => $newQuantity]);
            } else {
                $cartItem = CartItem::create([
                    'cart_id' => $this->cart->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'attributes' => !empty($attributes) ? json_encode($attributes) : null
                ]);
                
                Log::info('Created cart item', ['cart_item_id' => $cartItem->id, 'product_id' => $product->id]);
            }

            // Refresh cart items
            $this->cart->load('items.product');
            
            return $this->getCartSummary();
            
        } catch (\Exception $e) {
            Log::error('Add to cart failed: ' . $e->getMessage());
            throw $e;
        }
    }


    public function updateQuantity(int $itemId, int $quantity): array
    {
        $this->initializeCart();
        
        try {
            $cartItem = CartItem::where('cart_id', $this->cart->id)
                               ->where('id', $itemId)
                               ->firstOrFail();
            
            if ($quantity <= 0) {
                $cartItem->delete();
                Log::info('Removed cart item', ['cart_item_id' => $itemId]);
            } else {
                // Check stock limit
                if ($quantity > $cartItem->product->stock_quantity) {
                    throw new \Exception("Only {$cartItem->product->stock_quantity} items available in stock.");
                }
                
                $cartItem->update(['quantity' => $quantity]);
                Log::info('Updated cart item quantity', ['cart_item_id' => $itemId, 'quantity' => $quantity]);
            }

            // Refresh cart items
            $this->cart->load('items.product');
            
            return $this->getCartSummary();
            
        } catch (\Exception $e) {
            Log::error('Update cart quantity failed: ' . $e->getMessage());
            throw $e;
        }
    }


    public function removeItem(int $itemId): array
    {
        $this->initializeCart();
        
        try {
            CartItem::where('cart_id', $this->cart->id)
                    ->where('id', $itemId)
                    ->delete();
                    
            $this->cart->load('items.product');
            Log::info('Removed cart item', ['cart_item_id' => $itemId]);
            
            return $this->getCartSummary();
            
        } catch (\Exception $e) {
            Log::error('Remove cart item failed: ' . $e->getMessage());
            throw $e;
        }
    }


    public function clearCart(): array
    {
        $this->initializeCart();
        
        try {
            CartItem::where('cart_id', $this->cart->id)->delete();
            $this->cart->load('items.product');
            Log::info('Cleared cart', ['cart_id' => $this->cart->id]);
            
            return $this->getCartSummary();
            
        } catch (\Exception $e) {
            Log::error('Clear cart failed: ' . $e->getMessage());
            throw $e;
        }
    }


    public function getCartSummary(): array
    {
        $this->initializeCart();
        
        try {
            $items = [];
            $totalQuantity = 0;
            $subtotal = 0;
            
            foreach ($this->cart->items as $item) {
                if (!$item->product) {
                    continue;
                }
                
                $itemTotal = $item->product->price * $item->quantity;
                $subtotal += $itemTotal;
                $totalQuantity += $item->quantity;
                
                $items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => (float)$item->product->price,
                    'quantity' => (int)$item->quantity,
                    'total' => (float)$itemTotal,
                    'image' => $item->product->image,
                    'sku' => $item->product->sku,
                    'stock' => (int)$item->product->stock_quantity,
                    'attributes' => $item->attributes ? json_decode($item->attributes, true) : []
                ];
            }
            
            $tax = $subtotal * 0.10; // 10% tax
            $shipping = $subtotal > 100 ? 0 : 10;
            $total = $subtotal + $tax + $shipping;
            
            return [
                'success' => true,
                'items' => $items,
                'total_items' => count($items),
                'total_quantity' => $totalQuantity,
                'subtotal' => (float)$subtotal,
                'tax' => (float)$tax,
                'shipping' => (float)$shipping,
                'total' => (float)$total,
                'cart_id' => $this->cart->id
            ];
            
        } catch (\Exception $e) {
            Log::error('Get cart summary failed: ' . $e->getMessage());
            return [
                'success' => true,
                'items' => [],
                'total_items' => 0,
                'total_quantity' => 0,
                'subtotal' => 0,
                'tax' => 0,
                'shipping' => 0,
                'total' => 0,
                'cart_id' => null
            ];
        }
    }

    /**
     * Get cart item count
     */
    public function getCartCount(): int
    {
        $this->initializeCart();
        
        try {
            return $this->cart->items->sum('quantity');
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get raw cart object
     */
    public function getCart()
    {
        $this->initializeCart();
        return $this->cart;
    }

    /**
     * Get cart items
     */
    public function getCartItems()
    {
        $this->initializeCart();
        return $this->cart->items;
    }
    

    public function refreshCart(): void
    {
        $this->initialized = false;
        $this->cart = null;
        $this->initializeCart();
    }
}