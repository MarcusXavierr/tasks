<?php

use App\Models\Task;
use App\Models\User;

test('unauthenticated users cannot access dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/login');
});

test('authenticated users can access dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('tasks')
    );
});

test('dashboard shows empty tasks when no tasks exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks', [])
    );
});

test('dashboard displays tasks when tasks exist', function () {
    $user = User::factory()->create();
    
    $tasks = Task::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('tasks', 3)
        ->where('tasks.0.title', $tasks->first()->title)
        ->where('tasks.0.status', $tasks->first()->status)
        ->where('tasks.0.priority', $tasks->first()->priority)
    );
});

test('tasks are ordered by created_at desc', function () {
    $user = User::factory()->create();
    
    $oldTask = Task::factory()->create(['created_at' => now()->subDay()]);
    $newTask = Task::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('tasks', 2)
        ->where('tasks.0.id', $newTask->id)
        ->where('tasks.1.id', $oldTask->id)
    );
});

test('dashboard shows tasks with all required fields', function () {
    $user = User::factory()->create();
    
    $task = Task::factory()->create([
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->addWeek()
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('tasks', 1)
        ->where('tasks.0.title', 'Test Task')
        ->where('tasks.0.status', 'pending')
        ->where('tasks.0.priority', 'high')
        ->has('tasks.0.due_date')
    );
});