@php
    $actionColors = [
        'created' => 'success',
        'updated' => 'info',
        'deleted' => 'danger',
        'viewed' => 'secondary',
        'login' => 'primary',
        'logout' => 'warning',
        'failed' => 'danger',
        'processed' => 'success'
    ];
    
    $actionIcons = [
        'created' => 'fa-plus-circle',
        'updated' => 'fa-edit',
        'deleted' => 'fa-trash-alt',
        'viewed' => 'fa-eye',
        'login' => 'fa-sign-in-alt',
        'logout' => 'fa-sign-out-alt',
        'failed' => 'fa-exclamation-triangle',
        'processed' => 'fa-cog'
    ];
@endphp

<div class="card shadow-sm mb-3">
    <div class="card-header bg-{{ $actionColors[$log->action] ?? 'secondary' }} text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas {{ $actionIcons[$log->action] ?? 'fa-circle' }} me-2"></i>
                <strong>{{ ucfirst($log->action) }}</strong> - {{ ucfirst($log->module) }}
            </div>
            <small>
                <i class="fas fa-clock me-1"></i>
                {{ $log->created_at->format('F d, Y H:i:s') }}
            </small>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-user me-2"></i>User Information</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="120">Name:</th>
                        <td>{{ $log->user_name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $log->user_email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge bg-{{ $log->user_role == 'Admin' ? 'warning' : 'info' }}">{{ $log->user_role }}</span></td>
                    </tr>
                </table>
                
                <h6 class="mt-3"><i class="fas fa-desktop me-2"></i>Technical Details</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="120">IP Address:</th>
                        <td><code>{{ $log->ip_address }}</code></td>
                    </tr>
                    <tr>
                        <th>User Agent:</th>
                        <td><small>{{ Str::limit($log->user_agent, 100) }}</small></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge bg-{{ $log->status == 'success' ? 'success' : 'danger' }}">
                                {{ ucfirst($log->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h6><i class="fas fa-info-circle me-2"></i>Activity Details</h6>
                <table class="table table-sm">
                    <tr>
                        <th width="120">Action:</th>
                        <td>{{ ucfirst($log->action) }}</td>
                    </tr>
                    <tr>
                        <th>Module:</th>
                        <td>{{ ucfirst($log->module) }}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>{{ $log->description }}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        @if($log->old_data || $log->new_data)
            <hr>
            <h6><i class="fas fa-code-branch me-2"></i>Data Changes</h6>
            <div class="row">
                @if($log->old_data)
                <div class="col-md-6">
                    <div class="alert alert-secondary">
                        <strong><i class="fas fa-history"></i> Old Data:</strong>
                        <pre class="mb-0 mt-2 small"><code>{{ json_encode($log->old_data, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
                @endif
                
                @if($log->new_data)
                <div class="col-md-6">
                    <div class="alert alert-success">
                        <strong><i class="fas fa-check-circle"></i> New Data:</strong>
                        <pre class="mb-0 mt-2 small"><code>{{ json_encode($log->new_data, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                </div>
                @endif
            </div>
        @endif
        
        @if($log->metadata)
            <hr>
            <h6><i class="fas fa-tags me-2"></i>Metadata</h6>
            <div class="alert alert-info">
                <pre class="mb-0 small"><code>{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</code></pre>
            </div>
        @endif
    </div>
</div>