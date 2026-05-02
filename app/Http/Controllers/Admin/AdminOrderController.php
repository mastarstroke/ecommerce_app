<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminOrderController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->middleware(['auth', 'admin']);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::with('user');
        
        // Search filter
        if ($request->filled('search')) {
            $query->where('order_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('user', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                  });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Payment status filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        
        // Date filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        $orders = $query->latest()->paginate(20);
        
        // Get statistics
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
        ];
        
        return view('admin.orders.index', compact('orders', 'stats'));
    }

    /**
     * Display the specified order
     */
    public function show($id)
    {
        $order = Order::with('items.product', 'user')->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,declined,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);
        
        Log::info('Order status updated by admin', [
            'order_id' => $order->id,
            'new_status' => $request->status,
            'admin_id' => auth()->id()
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order status updated successfully!'
            ]);
        }
        
        return redirect()->back()->with('success', 'Order status updated successfully!');
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,failed'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['payment_status' => $request->payment_status]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully!'
            ]);
        }
        
        return redirect()->back()->with('success', 'Payment status updated successfully!');
    }
}