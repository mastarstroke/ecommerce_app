@extends('admin.layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Total Revenue</h6>
                        <h2 class="display-6 mb-0">${{ number_format($stats['total_revenue'], 2) }}</h2>
                    </div>
                    <i class="fas fa-dollar-sign fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Total Orders</h6>
                        <h2 class="display-6 mb-0">{{ $stats['total_orders'] }}</h2>
                    </div>
                    <i class="fas fa-shopping-cart fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Total Users</h6>
                        <h2 class="display-6 mb-0">{{ $stats['total_users'] }}</h2>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card stat-card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Total Products</h6>
                        <h2 class="display-6 mb-0">{{ $stats['total_products'] }}</h2>
                    </div>
                    <i class="fas fa-box fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Second Row Stats -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['pending_orders'] }}</h3>
                <small class="text-muted">Pending Orders</small>
                <div class="mt-2">
                    <span class="badge bg-warning">{{ $stats['pending_orders'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['processing_orders'] }}</h3>
                <small class="text-muted">Processing</small>
                <div class="mt-2">
                    <span class="badge bg-info">{{ $stats['processing_orders'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['completed_orders'] }}</h3>
                <small class="text-muted">Completed</small>
                <div class="mt-2">
                    <span class="badge bg-success">{{ $stats['completed_orders'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['cancelled_orders'] }}</h3>
                <small class="text-muted">Cancelled</small>
                <div class="mt-2">
                    <span class="badge bg-secondary">{{ $stats['cancelled_orders'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['low_stock_products'] }}</h3>
                <small class="text-muted">Low Stock</small>
                <div class="mt-2">
                    <span class="badge bg-danger">{{ $stats['low_stock_products'] }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card">
            <div class="card-body text-center">
                <h3 class="mb-0">{{ $stats['out_of_stock'] }}</h3>
                <small class="text-muted">Out of Stock</small>
                <div class="mt-2">
                    <span class="badge bg-dark">{{ $stats['out_of_stock'] }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-8 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Revenue Overview</h5>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Orders by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="ordersPieChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Orders & Top Products -->
<div class="row">
    <div class="col-md-7 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Recent Orders</h5>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-link">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr>
                                    <td><strong>#{{ $order->order_number }}</strong></td>
                                    <td>{{ $order->user->name }}</td>
                                    <td>${{ number_format($order->total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $order->getStatusBadgeAttribute() }}">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $order->created_at->format('M d, H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Top Selling Products</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($topProducts as $product)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ $product->name }}</h6>
                                    <small class="text-muted">Sold: {{ $product->total_sold }} units</small>
                                </div>
                                <div class="text-end">
                                    <strong>${{ number_format($product->price, 2) }}</strong>
                                    <br>
                                    <small class="text-success">+{{ $product->total_sold * $product->price }}</small>
                                </div>
                            </div>
                            <div class="progress mt-2" style="height: 5px;">
                                @php
                                    $maxSold = $topProducts->max('total_sold');
                                    $percentage = ($product->total_sold / $maxSold) * 100;
                                @endphp
                                <div class="progress-bar bg-primary" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Customers & Daily Orders -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Recent Customers</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($recentCustomers as $customer)
                        <div class="list-group-item">
                            <div class="d-flex align-items-center">
                                <img src="{{ $customer->avatar }}" class="rounded-circle me-3" style="width: 40px; height: 40px;">
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">{{ $customer->name }}</h6>
                                    <small class="text-muted">{{ $customer->email }}</small>
                                </div>
                                <small class="text-muted">{{ $customer->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Daily Orders (This Week)</h5>
            </div>
            <div class="card-body">
                <canvas id="dailyOrdersChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlyRevenue, 'month')) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode(array_column($monthlyRevenue, 'revenue')) !!},
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
    
    // Orders Pie Chart
    const pieCtx = document.getElementById('ordersPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Pending', 'Processing', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $ordersByStatus['pending'] }},
                    {{ $ordersByStatus['processing'] }},
                    {{ $ordersByStatus['completed'] }},
                    {{ $ordersByStatus['cancelled'] }}
                ],
                backgroundColor: ['#ffc107', '#17a2b8', '#28a745', '#6c757d'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
    
    // Daily Orders Chart
    const dailyCtx = document.getElementById('dailyOrdersChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($dailyOrders, 'day')) !!},
            datasets: [{
                label: 'Orders',
                data: {!! json_encode(array_column($dailyOrders, 'orders')) !!},
                backgroundColor: '#667eea',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>
@endsection