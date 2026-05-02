<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;

trait LogsActivity
{

    protected function logActivity(
        string $action,
        string $module,
        string $description = null,
        array $oldData = null,
        array $newData = null,
        array $metadata = null,
        string $status = 'success'
    ) {
        try {
            $user = auth()->user();
            
            ActivityLog::create([
                'user_id' => $user ? $user->id : null,
                'user_name' => $user ? $user->name : 'Guest',
                'user_email' => $user ? $user->email : null,
                'user_role' => $user ? ($user->is_admin ? 'Admin' : 'Customer') : 'Guest',
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_data' => $oldData,
                'new_data' => $newData,
                'metadata' => $metadata,
                'status' => $status
            ]);
        } catch (\Exception $e) {
            // Log error silently without breaking the main flow
            \Log::error('Failed to log activity: ' . $e->getMessage());
        }
    }


    protected function logError(string $module, string $description, array $metadata = null)
    {
        $this->logActivity('failed', $module, $description, null, null, $metadata, 'failed');
    }


    protected function logCreated(string $module, string $description, $newData = null)
    {
        $this->logActivity(
            'created', 
            $module, 
            $description, 
            null, 
            $newData ? (is_array($newData) ? $newData : $newData->toArray()) : null, 
            null, 
            'success'
        );
    }


    protected function logUpdated(string $module, string $description, $oldData = null, $newData = null)
    {
        $this->logActivity(
            'updated', 
            $module, 
            $description, 
            $oldData ? (is_array($oldData) ? $oldData : $oldData->toArray()) : null, 
            $newData ? (is_array($newData) ? $newData : $newData->toArray()) : null, 
            null, 
            'success'
        );
    }


    protected function logDeleted(string $module, string $description, $oldData = null)
    {
        $this->logActivity(
            'deleted', 
            $module, 
            $description, 
            $oldData ? (is_array($oldData) ? $oldData : $oldData->toArray()) : null, 
            null, 
            null, 
            'success'
        );
    }


    protected function logViewed(string $module, string $description, array $metadata = null)
    {
        $this->logActivity('viewed', $module, $description, null, null, $metadata, 'success');
    }


    protected function logProcessed(string $module, string $description, array $metadata = null)
    {
        $this->logActivity('processed', $module, $description, null, null, $metadata, 'success');
    }


    protected function logLoggedIn(string $module, string $description, $user = null)
    {
        $userData = $user ? ($user instanceof \App\Models\User ? $user->toArray() : ['user_id' => $user]) : null;
        $this->logActivity('login', $module, $description, null, $userData, null, 'success');
    }


    protected function logLoggedOut(string $module, string $description, $user = null)
    {
        $userData = $user ? ($user instanceof \App\Models\User ? $user->toArray() : ['user_id' => $user]) : null;
        $this->logActivity('logout', $module, $description, null, $userData, null, 'success');
    }


    protected function logFailedLogin(string $module, string $description, array $metadata = null)
    {
        $this->logActivity('login_failed', $module, $description, null, null, $metadata, 'failed');
    }
}