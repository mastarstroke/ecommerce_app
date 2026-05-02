<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityMonitorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }


    public function index(Request $request)
    {
        $query = ActivityLog::query();

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('user_name', 'like', '%' . $request->search . '%')
                  ->orWhere('user_email', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('action', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->latest()->paginate(50);

        // Get statistics for dashboard
        $stats = [
            'total' => ActivityLog::count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActivityLog::whereMonth('created_at', now()->month)->count(),
            'by_module' => ActivityLog::select('module', DB::raw('count(*) as total'))
                ->groupBy('module')
                ->get(),
            'by_action' => ActivityLog::select('action', DB::raw('count(*) as total'))
                ->groupBy('action')
                ->get(),
            'by_status' => ActivityLog::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get(),
            'recent_activities' => ActivityLog::with('user')
                ->latest()
                ->limit(10)
                ->get(),
            'top_users' => ActivityLog::select('user_id', 'user_name', DB::raw('count(*) as total'))
                ->groupBy('user_id', 'user_name')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get(),
        ];

        // Get unique modules and actions for filters
        $modules = ActivityLog::distinct()->pluck('module');
        $actions = ActivityLog::distinct()->pluck('action');
        $users = User::select('id', 'name', 'email')->get();

        return view('admin.monitor.index', compact('logs', 'stats', 'modules', 'actions', 'users'));
    }


    public function getStats(Request $request)
    {
        $stats = [
            'total' => ActivityLog::count(),
            'last_hour' => ActivityLog::where('created_at', '>=', now()->subHour())->count(),
            'last_24h' => ActivityLog::where('created_at', '>=', now()->subDay())->count(),
            'last_7days' => ActivityLog::where('created_at', '>=', now()->subDays(7))->count(),
            'recent_activities' => ActivityLog::with('user')
                ->latest()
                ->limit(10)
                ->get(),
            'module_breakdown' => ActivityLog::select('module', DB::raw('count(*) as total'))
                ->groupBy('module')
                ->get(),
        ];

        if ($request->ajax()) {
            return response()->json($stats);
        }

        return $stats;
    }


    public function getFeed(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        
        $activities = ActivityLog::with('user')
            ->where('id', '>', $lastId)
            ->latest()
            ->limit(20)
            ->get();

        return response()->json([
            'activities' => $activities,
            'last_id' => $activities->isNotEmpty() ? $activities->first()->id : $lastId
        ]);
    }


    public function export(Request $request)
    {
        $query = ActivityLog::query();

        // Apply same filters as index
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $logs = $query->latest()->get();

        $filename = "activity_logs_" . date('Y-m-d_His') . ".csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Add CSV headers
        fputcsv($handle, [
            'ID', 'User', 'Email', 'Role', 'Action', 'Module', 
            'Description', 'Status', 'IP Address', 'Timestamp'
        ]);

        // Add data rows
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->id,
                $log->user_name,
                $log->user_email,
                $log->user_role,
                $log->action,
                $log->module,
                $log->description,
                $log->status,
                $log->ip_address,
                $log->created_at
            ]);
        }

        fclose($handle);
        exit;
    }


    public function clearOld(Request $request)
    {
        $days = $request->get('days', 30);
        
        $deleted = ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

        return response()->json([
            'success' => true,
            'message' => "Deleted {$deleted} logs older than {$days} days"
        ]);
    }


    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json($log);
        }
        
        return view('admin.monitor.show', compact('log'));
    }
}