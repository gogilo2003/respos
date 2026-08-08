<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('admin');

        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhere('entity_type', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        if ($action = $request->query('action')) {
            $query->where('action', $action);
        }

        $logs = $query->paginate(25)->through(fn ($log) => [
            'id' => $log->id,
            'user' => $log->user?->name ?? 'System',
            'action' => $log->action,
            'entity_type' => $log->entity_type,
            'entity_id' => $log->entity_id,
            'old_value' => $log->old_value,
            'new_value' => $log->new_value,
            'reason' => $log->reason,
            'ip_address' => $log->ip_address,
            'created_at' => $log->created_at ? $log->created_at->format('H:i:s, M d, Y') : 'N/A',
        ]);

        return Inertia::render('AuditLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action']),
        ]);
    }
}
