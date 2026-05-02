<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCart
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    public function handle(Login $event): void
    {
        // Refresh the cart service to load user's cart
        $this->cartService->refreshCart();
    }
}