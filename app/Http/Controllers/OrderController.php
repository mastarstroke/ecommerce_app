<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->middleware('auth');
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of user's orders
     */
    public function index()
    {
        $orders = $this->orderRepository->getUserOrders(Auth::id(), 10);
        
        return view('orders.index', compact('orders'));
    }

    /**
     * Display specific order details
     */
    public function show($orderNumber)
    {
        $order = $this->orderRepository->getOrderByNumber($orderNumber);
        
        // Ensure user owns this order or is admin
        if (Auth::id() !== $order->user_id && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized access to this order.');
        }
        
        return view('orders.show', compact('order'));
    }


    public function cancel($orderNumber)
    {
        try {
            $order = $this->orderRepository->cancelOrder($orderNumber);
            return redirect()->route('orders.show', $orderNumber)->with('success', 'Order has been cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

}