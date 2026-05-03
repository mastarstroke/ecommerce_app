<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutService
{
    protected $orderRepository;
    protected $productRepository;
    protected $cartService;

    public function __construct(
        OrderRepository $orderRepository,
        ProductRepository $productRepository,
        CartService $cartService
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }


    public function processCheckout($user, $orderData, $cartItems)
    {
        if (empty($cartItems)) {
            throw new \Exception('Your cart is empty');
        }

        return DB::transaction(function () use ($user, $orderData, $cartItems) {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
            
            $shippingAddress = $orderData['shipping_address'] . ', ' . $orderData['shipping_city'] . ', ' . $orderData['shipping_postal'];
            $billingAddress = $orderData['billing_address'] . ', ' . $orderData['billing_city'] . ', ' . $orderData['billing_postal'];
            
            // Create order
            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => $orderNumber,
                'subtotal' => $orderData['subtotal'],
                'tax' => $orderData['tax'],
                'shipping_cost' => $orderData['shipping_cost'],
                'total' => $orderData['total'],
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $orderData['payment_method'],
                'shipping_address' => $shippingAddress,
                'billing_address' => $billingAddress,
                'notes' => $orderData['notes'] ?? null
            ]);
            
            // Create order items
            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['total'],
                    'attributes' => json_encode($item['attributes'] ?? [])
                ]);
                
                // Update product stock
                $product = Product::find($item['product_id']);
                if ($product) {
                    $product->decrement('stock_quantity', $item['quantity']);
                }
            }
            
            $this->cartService->clearCart();
            
            return $order;
        });
    }
}