<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id', 'user_name', 'user_email', 'user_role',
        'ip_address', 'user_agent', 'action', 'module',
        'description', 'old_data', 'new_data', 'metadata', 'status'
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function getActionIconAttribute(): string
    {
        $icons = [
            'created' => 'fa-plus-circle text-success',
            'updated' => 'fa-edit text-info',
            'deleted' => 'fa-trash-alt text-danger',
            'login' => 'fa-sign-in-alt text-primary',
            'logout' => 'fa-sign-out-alt text-warning',
            'viewed' => 'fa-eye text-secondary',
            'processed' => 'fa-cog text-primary',
            'failed' => 'fa-exclamation-triangle text-danger',
            'exported' => 'fa-download text-success',
            'imported' => 'fa-upload text-info',
        ];
        return $icons[$this->action] ?? 'fa-circle text-secondary';
    }

    public function getModuleIconAttribute(): string
    {
        $icons = [
            'auth' => 'fa-user-lock',
            'product' => 'fa-box',
            'category' => 'fa-tags',
            'order' => 'fa-shopping-cart',
            'user' => 'fa-users',
            'cart' => 'fa-shopping-basket',
            'payment' => 'fa-credit-card',
            'dashboard' => 'fa-tachometer-alt',
            'report' => 'fa-chart-line',
            'settings' => 'fa-cogs',
        ];
        return $icons[$this->module] ?? 'fa-circle-info';
    }
}