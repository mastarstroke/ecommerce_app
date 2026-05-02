<?php

namespace App\Http\Controllers;

use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Traits\LogsActivity;

class OrderController extends Controller
{
    use LogsActivity;
    
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->middleware('auth');
        $this->orderRepository = $orderRepository;
    }

    public function index()
    {
        $orders = $this->orderRepository->getUserOrders(Auth::id(), 10);
        
        $this->logActivity(
            'viewed',
            'order',
            'User viewed their orders list',
            null,
            null,
            [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
                'orders_count' => $orders->total(),
                'orders_page' => $orders->currentPage()
            ],
            'success'
        );
        
        return view('orders.index', compact('orders'));
    }

    public function show($orderNumber)
    {
        try {
            $order = $this->orderRepository->getOrderByNumber($orderNumber);
            
            if (Auth::id() !== $order->user_id && !Auth::user()->is_admin) {
                $this->logActivity(
                    'failed',
                    'order',
                    'Unauthorized access attempt to order details',
                    null,
                    null,
                    [
                        'user_id' => Auth::id(),
                        'user_name' => Auth::user()->name,
                        'attempted_order_number' => $orderNumber,
                        'order_owner_id' => $order->user_id
                    ],
                    'failed'
                );
                
                abort(403, 'Unauthorized access to this order.');
            }
            
            $this->logActivity(
                'viewed',
                'order',
                "User viewed order #{$order->order_number} details",
                null,
                $order->toArray(),
                [
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name,
                    'order_number' => $order->order_number,
                    'order_status' => $order->status,
                    'order_total' => $order->total,
                    'payment_status' => $order->payment_status,
                    'items_count' => $order->items->count()
                ],
                'success'
            );
            
            return view('orders.show', compact('order'));
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Failed to view order: {$e->getMessage()}",
                null,
                null,
                [
                    'user_id' => Auth::id(),
                    'order_number' => $orderNumber,
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->route('orders.index')->with('error', 'Order not found');
        }
    }

    public function cancel($orderNumber)
    {
        try {
            $order = $this->orderRepository->getOrderByNumber($orderNumber);
            
            if (Auth::id() !== $order->user_id && !Auth::user()->is_admin) {
                $this->logActivity(
                    'failed',
                    'order',
                    'Unauthorized cancellation attempt',
                    null,
                    null,
                    [
                        'user_id' => Auth::id(),
                        'order_number' => $orderNumber,
                        'order_owner_id' => $order->user_id
                    ],
                    'failed'
                );
                
                return redirect()->back()->with('error', 'You are not authorized to cancel this order.');
            }
            
            if ($order->status !== 'pending') {
                $this->logActivity(
                    'failed',
                    'order',
                    "Order cancellation failed - invalid status",
                    ['order_status' => $order->status],
                    null,
                    [
                        'order_number' => $orderNumber,
                        'current_status' => $order->status,
                        'user_id' => Auth::id()
                    ],
                    'failed'
                );
                
                return redirect()->back()->with('error', 'Only pending orders can be cancelled.');
            }
            
            $oldData = $order->toArray();
            $cancelledOrder = $this->orderRepository->cancelOrder($orderNumber);
            
            $this->logActivity(
                'updated',
                'order',
                "Order #{$orderNumber} was cancelled by user",
                $oldData,
                $cancelledOrder->toArray(),
                [
                    'order_id' => $cancelledOrder->id,
                    'order_number' => $cancelledOrder->order_number,
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name,
                    'original_status' => $oldData['status'],
                    'new_status' => 'cancelled',
                    'order_subtotal' => $cancelledOrder->subtotal,
                    'order_total' => $cancelledOrder->total,
                    'items_count' => $cancelledOrder->items->count()
                ],
                'success'
            );
            
            return redirect()->route('orders.show', $orderNumber)
                ->with('success', 'Order has been cancelled successfully.');
                
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Order cancellation failed: {$e->getMessage()}",
                null,
                null,
                [
                    'order_number' => $orderNumber,
                    'user_id' => Auth::id(),
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}