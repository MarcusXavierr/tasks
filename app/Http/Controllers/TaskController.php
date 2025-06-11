<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks
     */
    public function index()
    {
        $tasks = Task::orderBy('created_at', 'desc')->get();
        
        return Inertia::render('Dashboard', [
            'tasks' => $tasks
        ]);
    }
}
