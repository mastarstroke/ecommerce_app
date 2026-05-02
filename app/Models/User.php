<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'is_admin', 'phone', 'address', 
        'email_verified_at', 'avatar', 'two_factor_secret', 'two_factor_recovery_codes'
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_secret', 'two_factor_recovery_codes'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
    
    public function getAvatarAttribute(): string
    {
        return $this->avatar ?? 'https://ui-avatars.com/api/?background=667eea&color=fff&name=' . urlencode($this->name);
    }


    public function getStatsAttribute(): array
    {
        return [
            'total_orders' => $this->orders()->count(),
            'total_spent' => $this->orders()->where('status', 'completed')->sum('total'),
            'completed_orders' => $this->orders()->where('status', 'completed')->count(),
            'pending_orders' => $this->orders()->where('status', 'pending')->count(),
        ];
    }
}