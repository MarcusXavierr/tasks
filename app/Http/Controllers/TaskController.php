<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks with filtering, sorting, and pagination
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|string|in:pending,in-progress,completed',
            'priority' => 'nullable|string|in:low,medium,high',
            'sort_by' => 'nullable|string|in:due_date,created_at,priority',
            'sort_direction' => 'nullable|string|in:asc,desc',
            'per_page' => 'nullable|integer|min:5|max:50',
        ]);

        $query = Task::query()
            ->byStatus($filters['status'] ?? null)
            ->byPriority($filters['priority'] ?? null)
            ->sortBy(
                $filters['sort_by'] ?? null, 
                $filters['sort_direction'] ?? 'asc'
            );

        $perPage = $filters['per_page'] ?? 10;
        $tasks = $query->paginate($perPage);

        // Append filters to pagination links to preserve state
        $tasks->appends($request->query());

        return Inertia::render('Dashboard', [
            'tasks' => $tasks,
            'filters' => [
                'status' => $filters['status'] ?? null,
                'priority' => $filters['priority'] ?? null,
                'sort_by' => $filters['sort_by'] ?? null,
                'sort_direction' => $filters['sort_direction'] ?? null,
                'per_page' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new task
     */
    public function create()
    {
        return Inertia::render('Tasks/Create');
    }
}
