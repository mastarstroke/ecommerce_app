<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Category;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->middleware(['auth', 'admin']);
        $this->orderRepository = $orderRepository;
    }


    public function index()
    {
        // Get statistics
        $stats = [
            'total_users' => User::count(),
            'total_orders' => Order::count(),
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'low_stock_products' => Product::where('stock_quantity', '<', 10)->count(),
            'out_of_stock' => Product::where('stock_quantity', 0)->count(),
            'today_orders' => Order::whereDate('created_at', today())->count(),
            'today_revenue' => Order::whereDate('created_at', today())->where('payment_status', 'paid')->sum('total'),
        ];

        // Get recent orders
        $recentOrders = Order::with('user')
                            ->latest()
                            ->limit(10)
                            ->get();

        // Get top selling products
        $topProducts = DB::table('order_items')
                        ->join('products', 'order_items.product_id', '=', 'products.id')
                        ->select('products.id', 'products.name', 'products.price', 'products.images', 
                                DB::raw('SUM(order_items.quantity) as total_sold'))
                        ->groupBy('products.id', 'products.name', 'products.price', 'products.images')
                        ->orderBy('total_sold', 'DESC')
                        ->limit(5)
                        ->get();

        // Get recent customers
        $recentCustomers = User::where('is_admin', false)
                              ->latest()
                              ->limit(10)
                              ->get();

        // Get monthly revenue for chart
        $monthlyRevenue = $this->getMonthlyRevenue();

        // Get orders by status for pie chart
        $ordersByStatus = [
            'pending' => $stats['pending_orders'],
            'processing' => $stats['processing_orders'],
            'completed' => $stats['completed_orders'],
            'cancelled' => $stats['cancelled_orders'],
        ];

        // Get daily orders for current week
        $dailyOrders = $this->getDailyOrders();

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        return view('admin.dashboard.index', compact(
            'stats', 'recentOrders', 'topProducts', 'recentCustomers',
            'monthlyRevenue', 'ordersByStatus', 'dailyOrders', 'recentActivities'
        ));
    }

    /**
     * Get monthly revenue data
     */
    private function getMonthlyRevenue()
    {
        $revenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthRevenue = Order::whereYear('created_at', $date->year)
                                ->whereMonth('created_at', $date->month)
                                ->where('payment_status', 'paid')
                                ->sum('total');
            
            $revenue[] = [
                'month' => $date->format('M'),
                'revenue' => $monthRevenue
            ];
        }
        return $revenue;
    }

    /**
     * Get daily orders for current week
     */
    private function getDailyOrders()
    {
        $orders = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Order::whereDate('created_at', $date->toDateString())->count();
            
            $orders[] = [
                'day' => $date->format('D'),
                'orders' => $count
            ];
        }
        return $orders;
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];
        
        // Recent orders
        $recentOrders = Order::with('user')->latest()->limit(5)->get();
        foreach ($recentOrders as $order) {
            $activities[] = (object)[
                'type' => 'order',
                'message' => "New order #{$order->order_number} from {$order->user->name}",
                'amount' => $order->total,
                'time' => $order->created_at->diffForHumans(),
                'icon' => 'shopping-cart',
                'color' => 'success'
            ];
        }
        
        // New users
        $newUsers = User::where('is_admin', false)->latest()->limit(5)->get();
        foreach ($newUsers as $user) {
            $activities[] = (object)[
                'type' => 'user',
                'message' => "New user registered: {$user->name}",
                'email' => $user->email,
                'time' => $user->created_at->diffForHumans(),
                'icon' => 'user-plus',
                'color' => 'info'
            ];
        }
        
        // Low stock products
        $lowStock = Product::where('stock_quantity', '<', 10)->limit(5)->get();
        foreach ($lowStock as $product) {
            $activities[] = (object)[
                'type' => 'stock',
                'message' => "Product '{$product->name}' is running low on stock",
                'stock' => $product->stock_quantity,
                'time' => $product->updated_at->diffForHumans(),
                'icon' => 'exclamation-triangle',
                'color' => 'warning'
            ];
        }
        
        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b->time) - strtotime($a->time);
        });
        
        return array_slice($activities, 0, 10);
    }
}