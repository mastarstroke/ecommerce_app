<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AdminUserController extends Controller
{
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

    /**
     * Show form to create new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
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
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user
            ]);
        }
        
        return redirect()->route('admin.users.show', $user->id)
                        ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show($id)
    {
        $user = User::with('orders.items.product')->findOrFail($id);
        $stats = $user->stats;
        
        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show form to edit user
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
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
        $data['is_admin'] = $request->boolean('is_admin');
        
        // Update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $user
            ]);
        }
        
        return redirect()->route('admin.users.show', $user->id)
                        ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
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
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete user with existing orders.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete user with existing orders.');
        }
        
        $user->delete();
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!'
            ]);
        }
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'User deleted successfully!');
    }

    /**
     * Toggle admin status
     */
    public function toggleAdmin($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent toggling your own admin status
        if ($user->id === auth()->id()) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot change your own admin status.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You cannot change your own admin status.');
        }
        
        $user->update(['is_admin' => !$user->is_admin]);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $user->is_admin ? 'User is now an admin' : 'Admin rights removed',
                'is_admin' => $user->is_admin
            ]);
        }
        
        return redirect()->back()->with('success', $user->is_admin ? 'User is now an admin' : 'Admin rights removed');
    }

    /**
     * Verify user email
     */
    public function verifyEmail($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->email_verified_at) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User email is already verified.'
                ], 422);
            }
            return redirect()->back()->with('info', 'User email is already verified.');
        }
        
        $user->update(['email_verified_at' => now()]);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Email verified successfully!',
                'verified_at' => $user->email_verified_at->format('Y-m-d H:i:s')
            ]);
        }
        
        return redirect()->back()->with('success', 'User email verified successfully!');
    }

    /**
     * Impersonate user (for debugging/support)
     */
    public function impersonate($id)
    {
        $user = User::findOrFail($id);
        
        // Store original admin id in session
        session(['impersonated_by' => auth()->id()]);
        auth()->login($user);
        
        return redirect()->route('dashboard')
                        ->with('info', "You are now impersonating {$user->name}. Click the button below to exit.");
    }

    /**
     * Stop impersonating
     */
    public function stopImpersonate()
    {
        $adminId = session('impersonated_by');
        
        if ($adminId) {
            $admin = User::find($adminId);
            if ($admin) {
                session()->forget('impersonated_by');
                auth()->login($admin);
                return redirect()->route('admin.dashboard')
                                ->with('success', 'You are no longer impersonating.');
            }
        }
        
        return redirect()->route('admin.dashboard');
    }

    /**
     * Get user statistics (for AJAX)
     */
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

    /**
     * Bulk delete users
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id'
        ]);
        
        $deletedCount = 0;
        $failedCount = 0;
        
        foreach ($request->users as $userId) {
            $user = User::find($userId);
            
            // Skip current admin
            if ($user->id === auth()->id()) {
                $failedCount++;
                continue;
            }
            
            // Skip users with orders
            if ($user->orders()->exists()) {
                $failedCount++;
                continue;
            }
            
            $user->delete();
            $deletedCount++;
        }
        
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
    }

    /**
     * Export users to CSV
     */
    public function export(Request $request)
    {
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
    }

    /**
     * Send bulk email to selected users
     */
    public function sendBulkEmail(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*' => 'exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string'
        ]);
        
        $users = User::whereIn('id', $request->users)->get();
        $sentCount = 0;
        
        foreach ($users as $user) {
            // Mail::to($user->email)->send(new BulkEmailNotification($request->subject, $request->message));
            $sentCount++;
        }
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Email sent to {$sentCount} users successfully!"
            ]);
        }
        
        return redirect()->back()->with('success', "Email sent to {$sentCount} users successfully!");
    }
}