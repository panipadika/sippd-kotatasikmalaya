<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $action = $request->get('action');
        $query = ActivityLog::with('user')->latest();

        if ($action) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('activity.index', compact('logs', 'action'));
    }
}
