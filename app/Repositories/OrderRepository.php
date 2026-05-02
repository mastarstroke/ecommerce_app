<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class OrderRepository
{
    protected $model;

    public function __construct(Order $model)
    {
        $this->model = $model;
    }

    public function createOrder(array $orderData, array $items)
    {
        return DB::transaction(function () use ($orderData, $items) {
            $order = $this->model->create($orderData);
            
            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['price'],
                    'total' => $item['quantity'] * $item['price'],
                    'attributes' => $item['attributes'] ?? null
                ]);
            }
            
            return $order->load('items');
        });
    }

    public function getUserOrders(int $userId, int $perPage = 10)
    {
        return $this->model->where('user_id', $userId)->with('items.product')->latest()->paginate($perPage);
    }

    public function getOrderByNumber(string $orderNumber)
    {
        return $this->model->where('order_number', $orderNumber)->with('items.product', 'user')->firstOrFail();
    }

    public function updateOrderStatus(int $orderId, string $status)
    {
        $order = $this->model->findOrFail($orderId);
        $order->update(['status' => $status]);
        return $order;
    }

    public function getAllOrders(int $perPage = 15)
    {
        return $this->model->with('user')->latest() ->paginate($perPage);
    }

    public function cancelOrder(string $orderNumber)
    {
        $order = $this->getOrderByNumber($orderNumber);
        
        if ($order->status !== 'pending') {
            throw new \Exception('Only pending orders can be cancelled.');
        }
        
        $order->update(['status' => 'cancelled']);
        
        // Restore product stock if needed
        foreach ($order->items as $item) {
            $product = $item->product;
            $product->increment('stock_quantity', $item->quantity);
        }
        
        return $order;
    }

    public function getOrdersByStatus(string $status)
    {
        return $this->model->where('status', $status)->count();
    }

    public function getTotalRevenue()
    {
        return $this->model->where('payment_status', 'paid')->where('status', 'completed')->sum('total');
    }

    public function getTotalOrders()
    {
        return $this->model->count();
    }


    public function getOrderById(int $id)
    {
        return $this->model->with('items.product', 'user')->findOrFail($id);
    }

    public function getRecentOrders(int $limit = 5)
    {
        return $this->model->with('user')->latest()->limit($limit)->get();
    }

    public function getOrdersByDateRange($startDate, $endDate)
    {
        return $this->model->whereBetween('created_at', [$startDate, $endDate])->get();
    }

    public function getMonthlyRevenue(int $months = 6)
    {
        $revenue = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthRevenue = $this->model->whereMonth('created_at', $date->month)
                                        ->whereYear('created_at', $date->year)
                                        ->where('payment_status', 'paid')->sum('total');
            
            $revenue[] = [
                'month' => $date->format('M Y'),
                'revenue' => $monthRevenue
            ];
        }
        
        return $revenue;
    }
}