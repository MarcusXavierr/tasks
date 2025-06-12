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
        ->has('filters')
    );
});

test('dashboard shows empty tasks when no tasks exist', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.data', [])
        ->where('tasks.total', 0)
    );
});

test('dashboard displays tasks when tasks exist', function () {
    $user = User::factory()->create();

    $tasks = Task::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 3)
        ->has('tasks.data', 3)
        ->where('tasks.data.0.title', $tasks->first()->title)
        ->where('tasks.data.0.status', $tasks->first()->status)
        ->where('tasks.data.0.priority', $tasks->first()->priority)
    );
});

test('tasks are ordered by created_at desc by default', function () {
    $user = User::factory()->create();

    $oldTask = Task::factory()->create(['created_at' => now()->subDay()]);
    $newTask = Task::factory()->create(['created_at' => now()]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 2)
        ->where('tasks.data.0.id', $newTask->id)
        ->where('tasks.data.1.id', $oldTask->id)
    );
});

test('dashboard shows tasks with all required fields', function () {
    $user = User::factory()->create();

    $task = Task::factory()->create([
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->addWeek(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 1)
        ->where('tasks.data.0.title', 'Test Task')
        ->where('tasks.data.0.status', 'pending')
        ->where('tasks.data.0.priority', 'high')
        ->has('tasks.data.0.due_date')
    );
});

test('can filter tasks by status', function () {
    $user = User::factory()->create();

    Task::factory()->create(['status' => 'pending']);
    Task::factory()->create(['status' => 'completed']);
    Task::factory()->create(['status' => 'in-progress']);

    $response = $this->actingAs($user)->get('/dashboard?status=pending');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 1)
        ->where('tasks.data.0.status', 'pending')
        ->where('filters.status', 'pending')
    );
});

test('can filter tasks by priority', function () {
    $user = User::factory()->create();

    Task::factory()->create(['priority' => 'high']);
    Task::factory()->create(['priority' => 'low']);
    Task::factory()->create(['priority' => 'medium']);

    $response = $this->actingAs($user)->get('/dashboard?priority=high');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 1)
        ->where('tasks.data.0.priority', 'high')
        ->where('filters.priority', 'high')
    );
});

test('can combine status and priority filters', function () {
    $user = User::factory()->create();

    Task::factory()->create(['status' => 'pending', 'priority' => 'high']);
    Task::factory()->create(['status' => 'pending', 'priority' => 'low']);
    Task::factory()->create(['status' => 'completed', 'priority' => 'high']);

    $response = $this->actingAs($user)->get('/dashboard?status=pending&priority=high');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 1)
        ->where('tasks.data.0.status', 'pending')
        ->where('tasks.data.0.priority', 'high')
        ->where('filters.status', 'pending')
        ->where('filters.priority', 'high')
    );
});

test('can sort tasks by due_date ascending', function () {
    $user = User::factory()->create();

    $task1 = Task::factory()->create(['due_date' => now()->addDays(3)]);
    $task2 = Task::factory()->create(['due_date' => now()->addDays(1)]);
    $task3 = Task::factory()->create(['due_date' => now()->addDays(5)]);

    $response = $this->actingAs($user)->get('/dashboard?sort_by=due_date&sort_direction=asc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 3)
        ->where('tasks.data.0.id', $task2->id)
        ->where('tasks.data.1.id', $task1->id)
        ->where('tasks.data.2.id', $task3->id)
        ->where('filters.sort_by', 'due_date')
        ->where('filters.sort_direction', 'asc')
    );
});

test('can sort tasks by due_date descending', function () {
    $user = User::factory()->create();

    $task1 = Task::factory()->create(['due_date' => now()->addDays(3)]);
    $task2 = Task::factory()->create(['due_date' => now()->addDays(1)]);
    $task3 = Task::factory()->create(['due_date' => now()->addDays(5)]);

    $response = $this->actingAs($user)->get('/dashboard?sort_by=due_date&sort_direction=desc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 3)
        ->where('tasks.data.0.id', $task3->id)
        ->where('tasks.data.1.id', $task1->id)
        ->where('tasks.data.2.id', $task2->id)
        ->where('filters.sort_by', 'due_date')
        ->where('filters.sort_direction', 'desc')
    );
});

test('can sort tasks by priority', function () {
    $user = User::factory()->create();

    $lowTask = Task::factory()->create(['priority' => 'low']);
    $highTask = Task::factory()->create(['priority' => 'high']);
    $mediumTask = Task::factory()->create(['priority' => 'medium']);

    $response = $this->actingAs($user)->get('/dashboard?sort_by=priority&sort_direction=asc');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 3)
        ->where('tasks.data.0.priority', 'high')
        ->where('tasks.data.1.priority', 'medium')
        ->where('tasks.data.2.priority', 'low')
        ->where('filters.sort_by', 'priority')
        ->where('filters.sort_direction', 'asc')
    );
});

test('pagination works correctly', function () {
    $user = User::factory()->create();

    // Create 13 tasks to test pagination
    Task::factory()->count(13)->create();

    $response = $this->actingAs($user)->get('/dashboard?per_page=5');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('tasks.per_page', 5)
            ->where('tasks.current_page', 1)
            ->where('tasks.last_page', 3)
            ->has('tasks.data', 5)
            ->where('filters.per_page', 5)
        );
});

test('pagination preserves filters', function () {
    $user = User::factory()->create();

    Task::factory()->count(8)->create(['status' => 'pending']);
    Task::factory()->count(7)->create(['status' => 'completed']);

    $response = $this->actingAs($user)->get('/dashboard?status=pending&per_page=5&page=2');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 8)
        ->where('tasks.per_page', 5)
        ->where('tasks.current_page', 2)
        ->has('tasks.data', 3)
        ->where('filters.status', 'pending')
    );
});

test('invalid filter values are ignored', function () {
    $user = User::factory()->create();

    Task::factory()->count(3)->create();

    $response = $this->actingAs($user)->get('/dashboard?status=invalid&priority=invalid&sort_by=invalid');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('tasks.total', 3)
        ->where('filters.status', null)
        ->where('filters.priority', null)
        ->where('filters.sort_by', null)
    );
});

test('per_page has reasonable limits', function () {
    $user = User::factory()->create();

    Task::factory()->count(100)->create();

    // Test maximum limit
    $response = $this->actingAs($user)->get('/dashboard?per_page=100');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('filters.per_page', 10) // Should default to 10
    );

    // Test minimum limit
    $response = $this->actingAs($user)->get('/dashboard?per_page=1');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->where('filters.per_page', 10) // Should default to 10
    );
});

test('filters are properly initialized in response', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('filters')
        ->where('filters.status', null)
        ->where('filters.priority', null)
        ->where('filters.sort_by', null)
        ->where('filters.sort_direction', 'asc')
        ->where('filters.per_page', 10)
    );
});

// Task Creation Tests

test('can create a task with valid data', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->addWeek()->format('Y-m-d'),
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Task created successfully!',
        ])
        ->assertJsonStructure([
            'success',
            'message',
            'task' => [
                'id',
                'title',
                'status',
                'priority',
                'due_date',
                'created_at',
                'updated_at',
            ],
        ]);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
    ]);
});

test('task creation validates required due date', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});

test('task creation requires authentication', function () {
    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
    ];

    $response = $this->postJson('/tasks', $taskData);

    $response->assertStatus(401);
});

test('task creation validates required title', function () {
    $user = User::factory()->create();

    $taskData = [
        'status' => 'pending',
        'priority' => 'high',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('task creation validates title length', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => str_repeat('a', 256), // 256 characters
        'status' => 'pending',
        'priority' => 'high',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('task creation validates required status', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'priority' => 'high',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('task creation validates status values', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'invalid-status',
        'priority' => 'high',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('task creation validates required priority', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['priority']);
});

test('task creation validates priority values', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'invalid-priority',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['priority']);
});

test('task creation validates due date format', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => 'invalid-date',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});

test('task creation validates due date is after today', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->subDay()->format('Y-m-d'),
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});

test('task creation validates due date is not today', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Test Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->format('Y-m-d'),
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});

test('task creation accepts all valid status values', function () {
    $user = User::factory()->create();
    $validStatuses = ['pending', 'in-progress', 'completed'];

    foreach ($validStatuses as $status) {
        $taskData = [
            'title' => "Test Task with {$status} status",
            'status' => $status,
            'priority' => 'medium',
            'due_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
            ->postJson('/tasks', $taskData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => "Test Task with {$status} status",
            'status' => $status,
        ]);
    }
});

test('task creation accepts all valid priority values', function () {
    $user = User::factory()->create();
    $validPriorities = ['low', 'medium', 'high'];

    foreach ($validPriorities as $priority) {
        $taskData = [
            'title' => "Test Task with {$priority} priority",
            'status' => 'pending',
            'priority' => $priority,
            'due_date' => now()->addWeek()->format('Y-m-d'),
        ];

        $response = $this->actingAs($user)
            ->postJson('/tasks', $taskData);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => "Test Task with {$priority} priority",
            'priority' => $priority,
        ]);
    }
});

test('task creation returns proper error messages', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->postJson('/tasks', []);

    $response->assertStatus(422)
        ->assertJson([
            'errors' => [
                'title' => ['Task title is required.'],
                'status' => ['Task status is required.'],
                'priority' => ['Task priority is required.'],
                'due_date' => ['Due date is required.'],
            ],
        ]);
});

test('task creation handles multiple validation errors', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => '',
        'status' => 'invalid-status',
        'priority' => 'invalid-priority',
        'due_date' => 'invalid-date',
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title', 'status', 'priority', 'due_date']);
});

test('task creation with edge case title length', function () {
    $user = User::factory()->create();

    // Test with exactly 255 characters (should pass)
    $taskData = [
        'title' => str_repeat('a', 255),
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->addWeek()->format('Y-m-d'),
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(201);
});

test('task creation strips whitespace from title', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => '  Test Task  ',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->addWeek()->format('Y-m-d'),
    ];

    $response = $this->actingAs($user)
        ->postJson('/tasks', $taskData);

    $response->assertStatus(201);

    // Laravel trims strings by default via TrimStrings middleware
    $this->assertDatabaseHas('tasks', [
        'title' => 'Test Task',
    ]);
});

test('created task appears in dashboard immediately', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'New Dashboard Task',
        'status' => 'pending',
        'priority' => 'high',
        'due_date' => now()->addWeek()->format('Y-m-d'),
    ];

    // Create the task
    $this->actingAs($user)
        ->postJson('/tasks', $taskData)
        ->assertStatus(201);

    // Verify it appears in dashboard
    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertStatus(200)
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('tasks.total', 1)
            ->where('tasks.data.0.title', 'New Dashboard Task')
            ->where('tasks.data.0.status', 'pending')
            ->where('tasks.data.0.priority', 'high')
        );
});

test('task creation via form submission redirects to dashboard with success message', function () {
    $user = User::factory()->create();

    $taskData = [
        'title' => 'Form Submitted Task',
        'status' => 'pending',
        'priority' => 'medium',
        'due_date' => now()->addWeek()->format('Y-m-d'),
    ];

    $response = $this->actingAs($user)
        ->post('/tasks', $taskData);

    $response->assertRedirect('/dashboard')
        ->assertSessionHas('success', 'Task created successfully!');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Form Submitted Task',
        'status' => 'pending',
        'priority' => 'medium',
    ]);
});
