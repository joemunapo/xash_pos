<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $companyId = $user->company_id;

        $logs = ActivityLog::where('company_id', $companyId)
            ->with(['user:id,name,email', 'branch:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhere('model_type', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->action, function ($query, $action) {
                $query->where('action', $action);
            })
            ->when($request->user_id, function ($query, $userId) {
                $query->where('user_id', $userId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        // Get unique actions for filter
        $actions = ActivityLog::where('company_id', $companyId)
            ->select('action')
            ->distinct()
            ->pluck('action')
            ->map(fn ($action) => ['value' => $action, 'label' => ucfirst(str_replace('_', ' ', $action))])
            ->toArray();

        // Get users for filter
        $users = \App\Models\User::where('company_id', $companyId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn ($user) => ['value' => $user->id, 'label' => $user->name])
            ->toArray();

        return Inertia::render('Admin/ActivityLogs/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'action', 'user_id', 'date_from', 'date_to']),
            'actions' => $actions,
            'users' => $users,
        ]);
    }

    public function show(ActivityLog $activityLog): Response
    {
        $this->authorizeAccess($activityLog);

        $activityLog->load(['user:id,name,email', 'branch:id,name']);

        return Inertia::render('Admin/ActivityLogs/Show', [
            'log' => $activityLog,
        ]);
    }

    protected function authorizeAccess(ActivityLog $activityLog): void
    {
        if ($activityLog->company_id !== auth()->user()->company_id) {
            abort(403);
        }
    }
}
