<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Traits\LogsActivity;

class AdminOrderController extends Controller
{
    use LogsActivity;

    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->middleware(['auth', 'admin']);
        $this->orderRepository = $orderRepository;
    }


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
        
        // Log view action
        $this->logActivity(
            'viewed',
            'order',
            'Admin viewed orders list',
            null,
            null,
            [
                'filters' => $request->all(),
                'total_orders' => $stats['total'],
                'pending_orders' => $stats['pending'],
                'completed_orders' => $stats['completed']
            ],
            'success'
        );
        
        return view('admin.orders.index', compact('orders', 'stats'));
    }


    public function show($id)
    {
        try {
            $order = Order::with('items.product', 'user')->findOrFail($id);
            
            // Log view order details
            $this->logActivity(
                'viewed',
                'order',
                "Admin viewed order #{$order->order_number} details",
                null,
                $order->toArray(),
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->user->name,
                    'total' => $order->total
                ],
                'success'
            );
            
            return view('admin.orders.show', compact('order'));
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Failed to view order details: {$e->getMessage()}",
                null,
                null,
                ['order_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            return redirect()->route('admin.orders.index')
                ->with('error', 'Order not found');
        }
    }


    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'status' => 'required|in:pending,processing,completed,declined,cancelled'
            ]);

            $order = Order::with('user')->findOrFail($id);
            $oldStatus = $order->status;
            $oldData = $order->toArray();
            
            // Update status
            $order->update(['status' => $request->status]);
            
            // Prepare status messages for better logging
            $statusMessages = [
                'pending' => 'Order is now pending payment',
                'processing' => 'Order is now being processed',
                'completed' => 'Order has been completed',
                'declined' => 'Order has been declined',
                'cancelled' => 'Order has been cancelled'
            ];
            
            $description = "Order #{$order->order_number} status changed from '{$oldStatus}' to '{$request->status}'";
            
            // Log the status update with old and new data
            $this->logActivity(
                'updated',
                'order',
                $description,
                ['status' => $oldStatus],
                ['status' => $request->status],
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->user->name,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'total' => $order->total
                ],
                'success'
            );
            
            Log::info('Order status updated by admin', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order status updated successfully!',
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ]);
            }
            
            return redirect()->back()->with('success', 'Order status updated successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Failed to update order status: {$e->getMessage()}",
                $request->all(),
                null,
                [
                    'order_id' => $id,
                    'attempted_status' => $request->status,
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order status: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update order status');
        }
    }


    public function updatePaymentStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'payment_status' => 'required|in:pending,paid,failed'
            ]);

            $order = Order::with('user')->findOrFail($id);
            $oldPaymentStatus = $order->payment_status;
            $oldData = $order->toArray();
            
            // Update payment status
            $order->update(['payment_status' => $request->payment_status]);
            
            $description = "Order #{$order->order_number} payment status changed from '{$oldPaymentStatus}' to '{$request->payment_status}'";
            
            // Log the payment status update
            $this->logActivity(
                'updated',
                'payment',
                $description,
                ['payment_status' => $oldPaymentStatus],
                ['payment_status' => $request->payment_status],
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => $order->user->name,
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $request->payment_status,
                    'total' => $order->total
                ],
                'success'
            );
            
            Log::info('Payment status updated by admin', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => $request->payment_status,
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name
            ]);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment status updated successfully!',
                    'old_payment_status' => $oldPaymentStatus,
                    'new_payment_status' => $request->payment_status
                ]);
            }
            
            return redirect()->back()->with('success', 'Payment status updated successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'payment',
                "Failed to update payment status: {$e->getMessage()}",
                $request->all(),
                null,
                [
                    'order_id' => $id,
                    'attempted_payment_status' => $request->payment_status,
                    'error' => $e->getMessage()
                ],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update payment status: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update payment status');
        }
    }


    public function destroy($id)
    {
        try {
            $order = Order::with('user')->findOrFail($id);
            $orderData = $order->toArray();
            $orderNumber = $order->order_number;
            
            // Check if order can be deleted (only cancelled or declined orders)
            if (!in_array($order->status, ['cancelled', 'declined'])) {
                $this->logActivity(
                    'failed',
                    'order',
                    "Failed to delete order #{$orderNumber} - invalid status",
                    $orderData,
                    null,
                    ['order_id' => $id, 'status' => $order->status, 'reason' => 'invalid_status'],
                    'failed'
                );
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Only cancelled or declined orders can be deleted'
                    ], 422);
                }
                
                return redirect()->back()->with('error', 'Only cancelled or declined orders can be deleted');
            }
            
            // Delete order items first
            $order->items()->delete();
            
            // Delete order
            $order->delete();
            
            // Log the deletion
            $this->logActivity(
                'deleted',
                'order',
                "Order #{$orderNumber} was permanently deleted",
                $orderData,
                null,
                [
                    'order_id' => $id,
                    'order_number' => $orderNumber,
                    'customer' => $order->user->name,
                    'total' => $orderData['total']
                ],
                'success'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Order deleted successfully!'
                ]);
            }
            
            return redirect()->route('admin.orders.index')->with('success', 'Order deleted successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Failed to delete order: {$e->getMessage()}",
                null,
                null,
                ['order_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete order: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete order');
        }
    }


    public function export(Request $request)
    {
        try {
            $query = Order::with('user');
            
            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }
            
            $orders = $query->get();
            
            $filename = "orders_export_" . date('Y-m-d_His') . ".csv";
            $handle = fopen('php://output', 'w');
            
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            // Add CSV headers
            fputcsv($handle, [
                'Order #', 'Customer', 'Email', 'Total', 'Status', 
                'Payment Status', 'Payment Method', 'Date'
            ]);
            
            // Add data rows
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->user->name,
                    $order->user->email,
                    $order->total,
                    $order->status,
                    $order->payment_status,
                    $order->payment_method,
                    $order->created_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($handle);
            
            // Log export action
            $this->logActivity(
                'exported',
                'order',
                "Admin exported orders to CSV",
                null,
                null,
                [
                    'total_orders' => $orders->count(),
                    'filters' => $request->all()
                ],
                'success'
            );
            
            exit;
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Failed to export orders: {$e->getMessage()}",
                null,
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            return redirect()->back()->with('error', 'Failed to export orders');
        }
    }


    public function bulkUpdateStatus(Request $request)
    {
        try {
            $request->validate([
                'orders' => 'required|array',
                'orders.*' => 'exists:orders,id',
                'status' => 'required|in:pending,processing,completed,declined,cancelled'
            ]);
            
            $orders = Order::whereIn('id', $request->orders)->get();
            $updatedCount = 0;
            $updatedOrders = [];
            
            foreach ($orders as $order) {
                $oldStatus = $order->status;
                $order->update(['status' => $request->status]);
                $updatedCount++;
                
                $updatedOrders[] = [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status
                ];
            }
            
            // Log bulk update
            $this->logActivity(
                'updated',
                'order',
                "Bulk status update: {$updatedCount} orders updated to '{$request->status}'",
                null,
                null,
                [
                    'updated_count' => $updatedCount,
                    'new_status' => $request->status,
                    'orders' => $updatedOrders
                ],
                'success'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "{$updatedCount} orders updated successfully!"
                ]);
            }
            
            return redirect()->back()->with('success', "{$updatedCount} orders updated successfully!");
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'order',
                "Bulk status update failed: {$e->getMessage()}",
                $request->all(),
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update orders: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update orders');
        }
    }


    public function getStats(Request $request)
    {
        $stats = [
            'total' => Order::count(),
            'pending' => Order::where('status', 'pending')->count(),
            'processing' => Order::where('status', 'processing')->count(),
            'completed' => Order::where('status', 'completed')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())->sum('total'),
            'average_order_value' => Order::avg('total'),
        ];
        
        if ($request->ajax()) {
            return response()->json($stats);
        }
        
        return $stats;
    }
}