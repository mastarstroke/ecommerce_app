@extends('admin.layouts.admin')

@section('page-title', 'Activity Monitor')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Activity Monitor</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-primary me-2" onclick="refreshData()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <button class="btn btn-sm btn-outline-success me-2" onclick="exportLogs()">
            <i class="fas fa-download"></i> Export
        </button>
        <button class="btn btn-sm btn-outline-danger" onclick="clearOldLogs()">
            <i class="fas fa-trash"></i> Clear Old
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Total Activities</h6>
                        <h2 class="display-6 mb-0" id="total-activities">{{ number_format($stats['total']) }}</h2>
                    </div>
                    <i class="fas fa-chart-line fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">Today</h6>
                        <h2 class="display-6 mb-0">{{ number_format($stats['today']) }}</h2>
                    </div>
                    <i class="fas fa-calendar-day fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">This Week</h6>
                        <h2 class="display-6 mb-0">{{ number_format($stats['this_week']) }}</h2>
                    </div>
                    <i class="fas fa-calendar-week fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-title text-white-50">This Month</h6>
                        <h2 class="display-6 mb-0">{{ number_format($stats['this_month']) }}</h2>
                    </div>
                    <i class="fas fa-calendar-alt fa-3x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Activities by Module</h5>
            </div>
            <div class="card-body">
                <canvas id="moduleChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0 fw-bold">Activities by Action</h5>
            </div>
            <div class="card-body">
                <canvas id="actionChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" id="filterForm" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="User, action, description...">
            </div>
            <div class="col-md-2">
                <label class="form-label">Module</label>
                <select name="module" class="form-select">
                    <option value="">All Modules</option>
                    @foreach($modules as $module)
                        <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                            {{ ucfirst($module) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Filter
                </button>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <a href="{{ route('admin.monitor.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-sync"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Real-time Activity Feed -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-bolt text-warning"></i> Real-time Activity Feed
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush" id="activityFeed">
            @foreach($stats['recent_activities'] as $activity)
                <div class="list-group-item activity-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            @php
                                $actionIcons = [
                                    'created' => 'fa-plus-circle text-success',
                                    'updated' => 'fa-edit text-info',
                                    'deleted' => 'fa-trash-alt text-danger',
                                    'viewed' => 'fa-eye text-secondary',
                                    'login' => 'fa-sign-in-alt text-primary',
                                    'logout' => 'fa-sign-out-alt text-warning',
                                    'failed' => 'fa-exclamation-triangle text-danger',
                                    'processed' => 'fa-cog text-primary',
                                    'exported' => 'fa-download text-success'
                                ];
                                $iconClass = $actionIcons[$activity->action] ?? 'fa-circle text-secondary';
                            @endphp
                            <i class="fas {{ $iconClass }} fa-lg me-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $activity->user_name ?? 'System' }}</strong>
                                    <span class="text-muted mx-2">•</span>
                                    <span class="text-muted small">{{ ucfirst($activity->action) }}</span>
                                    <span class="text-muted mx-2">•</span>
                                    <span class="badge bg-secondary">{{ ucfirst($activity->module) }}</span>
                                </div>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 small">{{ $activity->description }}</p>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> {{ $activity->ip_address }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Activity Logs Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0 fw-bold">Activity History</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>IP Address</th>
                        <th>Time</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $statusColors = [
                            'success' => 'success',
                            'failed' => 'danger',
                            'pending' => 'warning',
                            'info' => 'info'
                        ];
                        $actionBadgeColors = [
                            'created' => 'success',
                            'updated' => 'info',
                            'deleted' => 'danger',
                            'viewed' => 'secondary',
                            'login' => 'primary',
                            'logout' => 'warning',
                            'failed' => 'danger',
                            'processed' => 'primary',
                            'exported' => 'success'
                        ];
                    @endphp
                    @forelse($logs as $log)
                    <tr>
                        <td>#{{ $log->id }}</td>
                        <td>
                            <div>
                                <strong>{{ $log->user_name }}</strong>
                                <br>
                                <small class="text-muted">{{ $log->user_email }}</small>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-{{ $actionBadgeColors[$log->action] ?? 'secondary' }}">
                                {{ ucfirst($log->action) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($log->module) }}</span>
                        </td>
                        <td>
                            <small>{{ Str::limit($log->description, 60) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ $statusColors[$log->status] ?? 'secondary' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                        <td><code>{{ $log->ip_address }}</code></td>
                        <td>
                            <small title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-info" onclick="viewDetails({{ $log->id }})">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                <p>No activity logs found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer">
        {{ $logs->withQueryString()->links() }}
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                Loading...
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let lastActivityId = {{ $stats['recent_activities']->first()->id ?? 0 }};
let autoRefreshInterval;

$(document).ready(function() {
    initCharts();
    startAutoRefresh();
});

function initCharts() {
    // Module Chart
    const moduleCtx = document.getElementById('moduleChart').getContext('2d');
    const moduleLabels = @json($stats['by_module']->pluck('module')->map(function($m) { return ucfirst($m); }));
    const moduleData = @json($stats['by_module']->pluck('total'));
    
    new Chart(moduleCtx, {
        type: 'bar',
        data: {
            labels: moduleLabels,
            datasets: [{
                label: 'Activities',
                data: moduleData,
                backgroundColor: '#667eea',
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
    
    // Action Chart
    const actionCtx = document.getElementById('actionChart').getContext('2d');
    const actionLabels = @json($stats['by_action']->pluck('action')->map(function($a) { return ucfirst($a); }));
    const actionData = @json($stats['by_action']->pluck('total'));
    const actionColors = {
        created: '#28a745',
        updated: '#17a2b8',
        deleted: '#dc3545',
        viewed: '#6c757d',
        login: '#007bff',
        logout: '#ffc107',
        failed: '#dc3545',
        processed: '#17a2b8',
        exported: '#28a745'
    };
    
    const backgroundColors = actionLabels.map(label => {
        const actionKey = label.toLowerCase();
        return actionColors[actionKey] || '#6c757d';
    });
    
    new Chart(actionCtx, {
        type: 'pie',
        data: {
            labels: actionLabels,
            datasets: [{
                data: actionData,
                backgroundColor: backgroundColors,
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right' }
            }
        }
    });
}

function startAutoRefresh() {
    autoRefreshInterval = setInterval(fetchNewActivities, 5000);
}

function fetchNewActivities() {
    $.get('{{ route("admin.monitor.feed") }}', { last_id: lastActivityId }, function(response) {
        if (response.activities && response.activities.length > 0) {
            response.activities.forEach(activity => {
                addActivityToFeed(activity);
            });
            lastActivityId = response.last_id;
            updateStats();
        }
    });
}

function addActivityToFeed(activity) {
    const actionIcons = {
        created: 'fa-plus-circle text-success',
        updated: 'fa-edit text-info',
        deleted: 'fa-trash-alt text-danger',
        viewed: 'fa-eye text-secondary',
        login: 'fa-sign-in-alt text-primary',
        logout: 'fa-sign-out-alt text-warning',
        failed: 'fa-exclamation-triangle text-danger',
        processed: 'fa-cog text-primary'
    };
    const iconClass = actionIcons[activity.action] || 'fa-circle text-secondary';
    
    const activityHtml = `
        <div class="list-group-item activity-item">
            <div class="d-flex align-items-center">
                <div class="flex-shrink-0">
                    <i class="fas ${iconClass} fa-lg me-3"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>${activity.user_name || 'System'}</strong>
                            <span class="text-muted mx-2">•</span>
                            <span class="text-muted small">${activity.action}</span>
                            <span class="text-muted mx-2">•</span>
                            <span class="badge bg-secondary">${ucfirst(activity.module)}</span>
                        </div>
                        <small class="text-muted">Just now</small>
                    </div>
                    <p class="mb-0 small">${activity.description || 'No description'}</p>
                    <small class="text-muted">
                        <i class="fas fa-map-marker-alt"></i> ${activity.ip_address}
                    </small>
                </div>
            </div>
        </div>
    `;
    
    $('#activityFeed').prepend(activityHtml);
    
    if ($('#activityFeed .activity-item').length > 20) {
        $('#activityFeed .activity-item:last').remove();
    }
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function updateStats() {
    $.get('{{ route("admin.monitor.stats") }}', function(data) {
        $('#total-activities').text(formatNumber(data.total));
    });
}

function formatNumber(num) {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
}

function refreshData() {
    location.reload();
}

function exportLogs() {
    const params = $('#filterForm').serialize();
    window.location.href = '{{ route("admin.monitor.export") }}?' + params;
}

function clearOldLogs() {
    Swal.fire({
        title: 'Clear Old Logs?',
        text: 'This will permanently delete logs older than 30 days.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, clear them!',
        input: 'number',
        inputLabel: 'Days to keep',
        inputValue: 30,
        inputPlaceholder: 'Enter number of days'
    }).then((result) => {
        if (result.isConfirmed) {
            const days = result.value || 30;
            $.ajax({
                url: '{{ route("admin.monitor.clear-old") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    days: days
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function() {
                    Swal.fire('Error!', 'Failed to clear logs', 'error');
                }
            });
        }
    });
}

function viewDetails(id) {
    $.get('{{ url("admin/monitor") }}/' + id, function(data) {
        let detailsHtml = `
            <div class="row">
                <div class="col-md-6">
                    <h6>Basic Information</h6>
                    <table class="table table-sm">
                        <tr><th>ID:</th><td>#${data.id}</td></tr>
                        <tr><th>User:</th><td>${data.user_name} (${data.user_email || 'N/A'})</td></tr>
                        <tr><th>Role:</th><td>${data.user_role || 'Guest'}</td></tr>
                        <tr><th>Action:</th><td>${data.action}</td></tr>
                        <tr><th>Module:</th><td>${data.module}</td></tr>
                        <tr><th>Status:</th><td>${data.status}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6>Technical Details</h6>
                    <table class="table table-sm">
                        <tr><th>IP Address:</th><td><code>${data.ip_address}</code></td></tr>
                        <tr><th>User Agent:</th><td><small>${data.user_agent || 'N/A'}</small></td></tr>
                        <tr><th>Time:</th><td>${data.created_at}</td></tr>
                    </table>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <h6>Description</h6>
                    <p>${data.description || 'No description provided'}</p>
                </div>
            </div>
        `;
        
        if (data.old_data || data.new_data) {
            detailsHtml += `
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Old Data</h6>
                        <pre class="bg-light p-2 rounded small" style="max-height: 300px; overflow: auto;">${JSON.stringify(data.old_data, null, 2) || 'N/A'}</pre>
                    </div>
                    <div class="col-md-6">
                        <h6>New Data</h6>
                        <pre class="bg-light p-2 rounded small" style="max-height: 300px; overflow: auto;">${JSON.stringify(data.new_data, null, 2) || 'N/A'}</pre>
                    </div>
                </div>
            `;
        }
        
        if (data.metadata) {
            detailsHtml += `
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Metadata</h6>
                        <pre class="bg-light p-2 rounded small" style="max-height: 200px; overflow: auto;">${JSON.stringify(data.metadata, null, 2)}</pre>
                    </div>
                </div>
            `;
        }
        
        $('#detailsContent').html(detailsHtml);
        $('#detailsModal').modal('show');
    }).fail(function() {
        $('#detailsContent').html('<div class="alert alert-danger">Failed to load activity details</div>');
    });
}
</script>
@endsection