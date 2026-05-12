<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Notification;
use App\Models\Task;
use App\Support\OrganizationScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TodayController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $today = Carbon::today();

        // Get tasks assigned to user for today
        $tasksForToday = Task::query()
            ->where('organization_id', $user->organization_id)
            ->where('assigned_to', $user->id)
            ->where(fn ($q) => $q
                ->whereDate('due_date', $today)
                ->orWhereNull('due_date')
            )
            ->whereNotIn('status', ['done', 'cancelled'])
            ->with(['ticket:id,reference,title', 'assignee:id,name'])
            ->orderBy('priority', 'desc')
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // Get overdue tasks
        $overdueTasks = Task::query()
            ->where('organization_id', $user->organization_id)
            ->where('assigned_to', $user->id)
            ->whereDate('due_date', '<', $today)
            ->whereNotIn('status', ['done', 'cancelled'])
            ->count();

        // Get reopened tasks
        $reopenedTasks = Task::query()
            ->where('organization_id', $user->organization_id)
            ->where('assigned_to', $user->id)
            ->where('status', 'reopened')
            ->with(['ticket:id,reference,title'])
            ->get();

        // Get tasks awaiting user action (pending_validation)
        $awaitingValidation = Task::query()
            ->where('organization_id', $user->organization_id)
            ->where('assigned_to', $user->id)
            ->where('status', 'pending_validation')
            ->count();

        // Get events for today where user participates
        $eventsToday = Event::query()
            ->with(['space:id,name', 'createdBy:id,name'])
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('start_at', $today)
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        // Get recent notifications
        $notifications = Notification::query()
            ->whereHas('recipients', fn ($q) => $q->where('user_id', $user->id)->whereNull('read_at'))
            ->latest()
            ->limit(5)
            ->get();

        return Inertia::render('Mobile/Today/Index', [
            'tasksForToday' => $tasksForToday,
            'overdueTasks' => $overdueTasks,
            'reopenedTasks' => $reopenedTasks,
            'awaitingValidation' => $awaitingValidation,
            'eventsToday' => $eventsToday,
            'notifications' => $notifications,
            'today' => $today->format('Y-m-d'),
        ]);
    }
}
