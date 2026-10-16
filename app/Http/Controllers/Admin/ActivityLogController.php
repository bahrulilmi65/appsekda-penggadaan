<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        if ($request->filled('module')) $query->where('module', $request->module);
        if ($request->filled('action')) $query->where('action', $request->action);
        if ($request->filled('user_id')) $query->where('user_id', $request->user_id);
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}
