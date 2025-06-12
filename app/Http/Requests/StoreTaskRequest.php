<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'status' => 'required|string|in:pending,in-progress,completed',
            'priority' => 'required|string|in:low,medium,high',
            'due_date' => 'required|date|after:today',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Task title is required.',
            'title.max' => 'Task title cannot exceed 255 characters.',
            'status.required' => 'Task status is required.',
            'status.in' => 'Task status must be one of: pending, in-progress, completed.',
            'priority.required' => 'Task priority is required.',
            'priority.in' => 'Task priority must be one of: low, medium, high.',
            'due_date.required' => 'Due date is required.',
            'due_date.date' => 'Due date must be a valid date.',
            'due_date.after' => 'Due date must be after today.',
        ];
    }
}
