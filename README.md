# Task Management Application

A modern task management system built with Laravel and Vue.js, featuring comprehensive task listing, filtering, sorting, and creation capabilities with real-time validation and optimistic UI updates.

## Getting Started

This application uses Laravel Sail for easy development environment setup. Follow these steps to get the application running locally:

### Prerequisites

- Docker and Docker Compose installed on your system

### Installation

1. **Install PHP dependencies**
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php82-composer:latest \
       composer install --ignore-platform-reqs
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Start the development environment**
   ```bash
   ./vendor/bin/sail up -d
   ```

4. **Generate application key**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

5. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

6. **Install frontend dependencies and build assets**
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```

### Accessing the Application

Once the setup is complete, you can access the application at:
- **Frontend**: http://localhost:4002
- **Database**: Available on port 3306 (if you need direct database access)

### Development Commands

- **Start development server**: `./vendor/bin/sail up -d`
- **Stop development server**: `./vendor/bin/sail down`
- **Run tests**: `./vendor/bin/sail artisan test`
- **View logs**: `./vendor/bin/sail logs`
- **Access container shell**: `./vendor/bin/sail shell`

## Features

### Task Listing (STORY-001)

A comprehensive task listing page that demonstrates effective data handling between Laravel backend and Vue.js frontend.

**Key Features:**
- **Task Filtering**: Filter tasks by status (pending, in-progress, completed) and priority (high, medium, low) with immediate UI updates
- **Task Sorting**: Sort tasks by due date, creation date, and priority using clickable column headers
- **Smart Pagination**: Pagination that preserves all filter and sort states when navigating between pages

**Technical Implementation:**
- **Backend**: Eloquent query builder with custom scopes for filtering and dynamic ordering
- **Frontend**: Inertia.js props handling with preserved state management across requests
- **State Management**: Pinia store implementation for managing filter/sort state across components

### Task Creation (STORY-002)

Advanced task creation feature with real-time validation and optimistic UI updates.

**Key Features:**
- **Real-time Validation**: Client-side validation with immediate feedback
- **Server-side Validation**: Comprehensive backend validation with custom error messages
- **Optimistic UI Updates**: Immediate UI feedback while processing requests
- **Modal Interface**: Clean, responsive modal design for task creation

**Validation Rules:**
- Title: Required, maximum 255 characters
- Status: Required, must be one of: pending, in-progress, completed
- Priority: Required, must be one of: low, medium, high
- Due Date: Required, must be after today

**Technical Implementation:**
- **Backend**: Custom `StoreTaskRequest` form request class with comprehensive validation
- **Frontend**: `TaskCreateModal.vue` component with dual validation approach
- **Error Handling**: Field-specific error highlighting and consistent messaging
- **Security**: CSRF protection and proper HTTP status codes

## Technology Stack

- **Backend**: Laravel (PHP)
- **Frontend**: Vue.js 3 with TypeScript
- **Bridge**: Inertia.js for seamless SPA experience
- **State Management**: Pinia
- **Styling**: Tailwind CSS
- **Development Environment**: Laravel Sail (Docker)
- **Database**: MySQL

## Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/TaskController.php
│   │   └── Requests/StoreTaskRequest.php
│   └── Models/Task.php
├── resources/
│   ├── js/
│   │   ├── Components/
│   │   │   ├── TaskCreateModal.vue
│   │   │   └── Modal.vue
│   │   └── Pages/
│   │       └── Dashboard.vue
│   └── views/
└── routes/web.php
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
