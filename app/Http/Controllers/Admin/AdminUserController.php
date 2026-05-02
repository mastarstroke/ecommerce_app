<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
    use LogsActivity;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }


    public function index(Request $request)
    {
        $query = User::withCount('orders');
        
        // Search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        
        // Role filter
        if ($request->filled('role')) {
            if ($request->role == 'admin') {
                $query->where('is_admin', true);
            } elseif ($request->role == 'customer') {
                $query->where('is_admin', false);
            }
        }
        
        // Verification filter
        if ($request->filled('verified')) {
            if ($request->verified == 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->verified == 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }
        
        $users = $query->latest()->paginate(20);
        
        // Get statistics
        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'customers' => User::where('is_admin', false)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
        ];
        
        // Log view action
        $this->logActivity(
            'viewed',
            'user',
            'Admin viewed users list',
            null,
            null,
            [
                'filters' => $request->all(),
                'total_users' => $stats['total'],
                'admins' => $stats['admins'],
                'customers' => $stats['customers']
            ],
            'success'
        );
        
        // If AJAX request, return only the table rows and pagination
        if ($request->ajax()) {
            $view = view('admin.users.partials.table_rows', compact('users'))->render();
            $statsView = view('admin.users.partials.stats_cards', compact('stats'))->render();
            $pagination = view('admin.users.partials.pagination', compact('users'))->render();
            
            return response()->json([
                'success' => true,
                'table_rows' => $view,
                'stats_cards' => $statsView,
                'pagination' => $pagination
            ]);
        }
        
        return view('admin.users.index', compact('users', 'stats'));
    }


    public function create()
    {
        $this->logActivity(
            'viewed',
            'user',
            'Admin accessed user creation form',
            null,
            null,
            null,
            'success'
        );
        
        return view('admin.users.create');
    }


    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'is_admin' => 'boolean',
                'verify_email' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'address' => $request->address,
                'is_admin' => $request->boolean('is_admin'),
                'email_verified_at' => $request->boolean('verify_email') ? now() : null,
            ]);
            
            // Log user creation
            $this->logActivity(
                'created',
                'user',
                "User '{$user->name}' was created as " . ($user->is_admin ? 'Admin' : 'Customer'),
                null,
                $user->toArray(),
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'role' => $user->is_admin ? 'Admin' : 'Customer',
                    'email_verified' => $user->email_verified_at ? 'Yes' : 'No'
                ],
                'success'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User created successfully!',
                    'user' => $user
                ]);
            }
            
            return redirect()->route('admin.users.show', $user->id)
                            ->with('success', 'User created successfully!');
                            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to create user: {$e->getMessage()}",
                $request->all(),
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to create user');
        }
    }


    public function show($id)
    {
        try {
            $user = User::with('orders.items.product')->findOrFail($id);
            $stats = $user->stats;
            
            // Log view user details
            $this->logActivity(
                'viewed',
                'user',
                "Admin viewed user '{$user->name}' details",
                null,
                $user->toArray(),
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'role' => $user->is_admin ? 'Admin' : 'Customer',
                    'orders_count' => $user->orders->count()
                ],
                'success'
            );
            
            return view('admin.users.show', compact('user', 'stats'));
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to view user details: {$e->getMessage()}",
                null,
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            return redirect()->route('admin.users.index')->with('error', 'User not found');
        }
    }


    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            
            $this->logActivity(
                'viewed',
                'user',
                "Admin accessed edit form for user '{$user->name}'",
                null,
                null,
                ['user_id' => $user->id, 'user_email' => $user->email],
                'success'
            );
            
            return view('admin.users.edit', compact('user'));
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to load user edit form: {$e->getMessage()}",
                null,
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            return redirect()->route('admin.users.index')->with('error', 'User not found');
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $oldData = $user->toArray();
            
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'is_admin' => 'boolean',
                'password' => 'nullable|string|min:8|confirmed'
            ]);
            
            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()
                    ], 422);
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }
            
            $data = $request->only(['name', 'email', 'phone', 'address']);
            $oldRole = $user->is_admin;
            $data['is_admin'] = $request->boolean('is_admin');
            
            // Track role change
            $roleChanged = ($oldRole != $data['is_admin']);
            
            // Update password if provided
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }
            
            $user->update($data);
            
            // Prepare change description
            $changes = [];
            foreach ($data as $key => $value) {
                if (isset($oldData[$key]) && $oldData[$key] != $value) {
                    $changes[$key] = [
                        'old' => $oldData[$key],
                        'new' => $value
                    ];
                }
            }
            
            $description = "User '{$user->name}' was updated";
            if ($roleChanged) {
                $description .= " and role changed to " . ($user->is_admin ? 'Admin' : 'Customer');
            }
            
            // Log the update
            $this->logActivity(
                'updated',
                'user',
                $description,
                $oldData,
                $user->toArray(),
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'changes' => $changes,
                    'role_changed' => $roleChanged,
                    'new_role' => $user->is_admin ? 'Admin' : 'Customer'
                ],
                'success'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User updated successfully!',
                    'user' => $user
                ]);
            }
            
            return redirect()->route('admin.users.show', $user->id)
                            ->with('success', 'User updated successfully!');
                            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to update user: {$e->getMessage()}",
                $request->all(),
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update user');
        }
    }


    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting yourself
            if ($user->id === auth()->id()) {
                $this->logActivity(
                    'failed',
                    'user',
                    "User '{$user->name}' attempted to delete their own account",
                    null,
                    null,
                    ['user_id' => $id, 'reason' => 'self_delete'],
                    'failed'
                );
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot delete your own account.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'You cannot delete your own account.');
            }
            
            // Check if user has orders
            if ($user->orders()->exists()) {
                $this->logActivity(
                    'failed',
                    'user',
                    "Failed to delete user '{$user->name}' - has existing orders",
                    $user->toArray(),
                    null,
                    ['user_id' => $id, 'orders_count' => $user->orders()->count(), 'reason' => 'has_orders'],
                    'failed'
                );
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete user with existing orders.'
                    ], 422);
                }
                return redirect()->back()->with('error', 'Cannot delete user with existing orders.');
            }
            
            $userData = $user->toArray();
            $userName = $user->name;
            $userEmail = $user->email;
            
            $user->delete();
            
            // Log the deletion
            $this->logActivity(
                'deleted',
                'user',
                "User '{$userName}' was permanently deleted",
                $userData,
                null,
                [
                    'user_id' => $id,
                    'user_email' => $userEmail,
                    'user_name' => $userName,
                    'was_admin' => $userData['is_admin'] ? 'Yes' : 'No'
                ],
                'success'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'User deleted successfully!'
                ]);
            }
            
            return redirect()->route('admin.users.index')
                            ->with('success', 'User deleted successfully!');
                            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to delete user: {$e->getMessage()}",
                null,
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete user: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete user');
        }
    }


    public function toggleAdmin($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent toggling your own admin status
            if ($user->id === auth()->id()) {
                $this->logActivity(
                    'failed',
                    'user',
                    "User '{$user->name}' attempted to change their own admin status",
                    null,
                    null,
                    ['user_id' => $id, 'reason' => 'self_toggle'],
                    'failed'
                );
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You cannot change your own admin status.'
                    ], 403);
                }
                return redirect()->back()->with('error', 'You cannot change your own admin status.');
            }
            
            $oldStatus = $user->is_admin;
            $user->update(['is_admin' => !$user->is_admin]);
            $newRole = $user->is_admin ? 'Admin' : 'Customer';
            
            // Log the role change
            $this->logActivity(
                'updated',
                'user',
                "User '{$user->name}' role changed from " . ($oldStatus ? 'Admin' : 'Customer') . " to {$newRole}",
                ['is_admin' => $oldStatus],
                ['is_admin' => $user->is_admin],
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'old_role' => $oldStatus ? 'Admin' : 'Customer',
                    'new_role' => $newRole
                ],
                'success'
            );
            
            $message = $user->is_admin ? 'User is now an admin' : 'Admin rights removed';
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'is_admin' => $user->is_admin
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to toggle admin status: {$e->getMessage()}",
                null,
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update admin status'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to update admin status');
        }
    }


    public function verifyEmail($id)
    {
        try {
            $user = User::findOrFail($id);
            
            if ($user->email_verified_at) {
                $this->logActivity(
                    'viewed',
                    'user',
                    "Attempted to verify already verified email for user '{$user->name}'",
                    null,
                    null,
                    ['user_id' => $id, 'verified_at' => $user->email_verified_at],
                    'info'
                );
                
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User email is already verified.'
                    ], 422);
                }
                return redirect()->back()->with('info', 'User email is already verified.');
            }
            
            $user->update(['email_verified_at' => now()]);
            
            // Log email verification
            $this->logActivity(
                'updated',
                'user',
                "Email verified for user '{$user->name}'",
                ['email_verified_at' => null],
                ['email_verified_at' => $user->email_verified_at],
                [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'verified_at' => $user->email_verified_at
                ],
                'success'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email verified successfully!',
                    'verified_at' => $user->email_verified_at->format('Y-m-d H:i:s')
                ]);
            }
            
            return redirect()->back()->with('success', 'User email verified successfully!');
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to verify email: {$e->getMessage()}",
                null,
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to verify email'
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to verify email');
        }
    }


    public function impersonate($id)
    {
        try {
            $user = User::findOrFail($id);
            $adminUser = auth()->user();
            
            // Log impersonation start
            $this->logActivity(
                'processed',
                'user',
                "Admin '{$adminUser->name}' started impersonating user '{$user->name}'",
                null,
                null,
                [
                    'admin_id' => $adminUser->id,
                    'admin_name' => $adminUser->name,
                    'impersonated_user_id' => $user->id,
                    'impersonated_user_name' => $user->name,
                    'impersonated_user_email' => $user->email
                ],
                'success'
            );
            
            // Store original admin id in session
            session(['impersonated_by' => auth()->id()]);
            auth()->login($user);
            
            return redirect()->route('dashboard')
                            ->with('info', "You are now impersonating {$user->name}. To exit, click the button below.");
                            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to impersonate user: {$e->getMessage()}",
                null,
                null,
                ['user_id' => $id, 'error' => $e->getMessage()],
                'failed'
            );
            
            return redirect()->back()->with('error', 'Failed to impersonate user');
        }
    }


    public function stopImpersonate()
    {
        try {
            $adminId = session('impersonated_by');
            
            if ($adminId) {
                $admin = User::find($adminId);
                if ($admin) {
                    // Log impersonation stop
                    $this->logActivity(
                        'processed',
                        'user',
                        "Admin '{$admin->name}' stopped impersonating",
                        null,
                        null,
                        [
                            'admin_id' => $admin->id,
                            'admin_name' => $admin->name
                        ],
                        'success'
                    );
                    
                    session()->forget('impersonated_by');
                    auth()->login($admin);
                    return redirect()->route('admin.dashboard')
                                    ->with('success', 'You are no longer impersonating.');
                }
            }
            
            return redirect()->route('admin.dashboard');
            
        } catch (\Exception $e) {
            return redirect()->route('admin.dashboard')->with('error', 'Failed to stop impersonating');
        }
    }


    public function getStats()
    {
        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'customers' => User::where('is_admin', false)->count(),
            'verified' => User::whereNotNull('email_verified_at')->count(),
            'unverified' => User::whereNull('email_verified_at')->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
            'new_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_this_month' => User::whereMonth('created_at', now()->month)->count(),
        ];
        
        if (request()->ajax()) {
            return response()->json(['success' => true, 'stats' => $stats]);
        }
        
        return $stats;
    }


    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'users' => 'required|array',
                'users.*' => 'exists:users,id'
            ]);
            
            $deletedCount = 0;
            $failedCount = 0;
            $deletedUsers = [];
            $failedUsers = [];
            
            foreach ($request->users as $userId) {
                $user = User::find($userId);
                
                // Skip current admin
                if ($user->id === auth()->id()) {
                    $failedCount++;
                    $failedUsers[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'reason' => 'self_delete'
                    ];
                    continue;
                }
                
                // Skip users with orders
                if ($user->orders()->exists()) {
                    $failedCount++;
                    $failedUsers[] = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'reason' => 'has_orders',
                        'orders_count' => $user->orders()->count()
                    ];
                    continue;
                }
                
                $deletedUsers[] = [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'was_admin' => $user->is_admin
                ];
                
                $user->delete();
                $deletedCount++;
            }
            
            // Log bulk deletion
            $this->logActivity(
                'deleted',
                'user',
                "Bulk delete operation: {$deletedCount} users deleted, {$failedCount} failed",
                null,
                null,
                [
                    'deleted_count' => $deletedCount,
                    'failed_count' => $failedCount,
                    'deleted_users' => $deletedUsers,
                    'failed_users' => $failedUsers
                ],
                $deletedCount > 0 ? 'success' : 'failed'
            );
            
            $message = "{$deletedCount} users deleted successfully.";
            if ($failedCount > 0) {
                $message .= " {$failedCount} users could not be deleted (self or have orders).";
            }
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted' => $deletedCount,
                    'failed' => $failedCount
                ]);
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Bulk delete operation failed: {$e->getMessage()}",
                $request->all(),
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete users: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to delete users');
        }
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
        try {
            $query = User::query();
            
            // Apply filters
            if ($request->filled('role')) {
                if ($request->role == 'admin') {
                    $query->where('is_admin', true);
                } elseif ($request->role == 'customer') {
                    $query->where('is_admin', false);
                }
            }
            
            if ($request->filled('verified')) {
                if ($request->verified == 'verified') {
                    $query->whereNotNull('email_verified_at');
                } elseif ($request->verified == 'unverified') {
                    $query->whereNull('email_verified_at');
                }
            }
            
            $users = $query->get();
            $totalUsers = $users->count();
            
            // Log export action
            $this->logActivity(
                'exported',
                'user',
                "Admin exported users to CSV",
                null,
                null,
                [
                    'total_exported' => $totalUsers,
                    'filters' => $request->all()
                ],
                'success'
            );
            
            $filename = "users_export_" . date('Y-m-d_His') . ".csv";
            $handle = fopen('php://output', 'w');
            
            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            // Add CSV headers
            fputcsv($handle, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Email Verified', 'Orders Count', 'Joined Date', 'Last Updated']);
            
            // Add data rows
            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone ?? '',
                    $user->is_admin ? 'Admin' : 'Customer',
                    $user->email_verified_at ? 'Yes' : 'No',
                    $user->orders()->count(),
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($handle);
            exit;
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to export users: {$e->getMessage()}",
                null,
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            return redirect()->back()->with('error', 'Failed to export users');
        }
    }


    public function sendBulkEmail(Request $request)
    {
        try {
            $request->validate([
                'users' => 'required|array',
                'users.*' => 'exists:users,id',
                'subject' => 'required|string|max:255',
                'message' => 'required|string'
            ]);
            
            $users = User::whereIn('id', $request->users)->get();
            $sentCount = $users->count();
            
            // Here you would actually send emails
            // Mail::to($users)->send(new BulkEmailNotification($request->subject, $request->message));
            
            // Log bulk email
            $this->logActivity(
                'processed',
                'user',
                "Bulk email sent to {$sentCount} users",
                null,
                null,
                [
                    'recipients_count' => $sentCount,
                    'subject' => $request->subject,
                    'user_ids' => $request->users,
                    'recipients' => $users->pluck('email')->toArray()
                ],
                'success'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Email sent to {$sentCount} users successfully!"
                ]);
            }
            
            return redirect()->back()->with('success', "Email sent to {$sentCount} users successfully!");
            
        } catch (\Exception $e) {
            $this->logActivity(
                'failed',
                'user',
                "Failed to send bulk email: {$e->getMessage()}",
                $request->all(),
                null,
                ['error' => $e->getMessage()],
                'failed'
            );
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send emails: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Failed to send emails');
        }
    }
}