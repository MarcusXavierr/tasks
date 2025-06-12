<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaskRequest;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks with filtering, sorting, and pagination
     */
    public function index(Request $request)
    {
        // Extract and sanitize filters without validation to ignore invalid values
        $status = $request->input('status');
        $priority = $request->input('priority');
        $sortBy = $request->input('sort_by');
        $sortDirection = $request->input('sort_direction', 'asc');
        $perPage = $request->input('per_page', 10);

        // Sanitize values
        $validStatus = in_array($status, ['pending', 'in-progress', 'completed']) ? $status : null;
        $validPriority = in_array($priority, ['low', 'medium', 'high']) ? $priority : null;
        $validSortBy = in_array($sortBy, ['due_date', 'created_at', 'priority']) ? $sortBy : null;
        $validSortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
        $validPerPage = is_numeric($perPage) && $perPage >= 5 && $perPage <= 50 ? (int) $perPage : 10;

        $query = Task::query()
            ->byStatus($validStatus)
            ->byPriority($validPriority)
            ->sortBy($validSortBy, $validSortDirection);

        $tasks = $query->paginate($validPerPage);

        // Append filters to pagination links to preserve state
        $tasks->appends($request->query());

        return Inertia::render('Dashboard', [
            'tasks' => $tasks,
            'filters' => [
                'status' => $validStatus,
                'priority' => $validPriority,
                'sort_by' => $validSortBy,
                'sort_direction' => $validSortDirection,
                'per_page' => $validPerPage,
            ],
        ]);
    }

    /**
     * Store a newly created task in storage
     */
    public function store(StoreTaskRequest $request): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $task = Task::create($request->validated());

        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => $task,
            ], 201);
        }

        return redirect()->route('dashboard')->with('success', 'Task created successfully!');
    }
}
