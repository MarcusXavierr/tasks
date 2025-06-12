<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'status',
        'priority',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    /**
     * Filter tasks by status
     */
    public function scopeByStatus(Builder $query, ?string $status): Builder
    {
        if ($status && in_array($status, ['pending', 'in-progress', 'completed'])) {
            return $query->where('status', $status);
        }

        return $query;
    }

    /**
     * Filter tasks by priority
     */
    public function scopeByPriority(Builder $query, ?string $priority): Builder
    {
        if ($priority && in_array($priority, ['low', 'medium', 'high'])) {
            return $query->where('priority', $priority);
        }

        return $query;
    }

    /**
     * Sort tasks by specified column and direction
     */
    public function scopeSortBy(Builder $query, ?string $sortBy, ?string $sortDirection = 'asc'): Builder
    {
        $validSortColumns = ['due_date', 'created_at', 'priority'];
        $validDirections = ['asc', 'desc'];

        if ($sortBy && in_array($sortBy, $validSortColumns)) {
            $direction = in_array($sortDirection, $validDirections) ? $sortDirection : 'asc';

            if ($sortBy === 'priority') {
                return $query->orderByRaw("
                    CASE priority
                        WHEN 'high' THEN 1
                        WHEN 'medium' THEN 2
                        WHEN 'low' THEN 3
                        ELSE 4
                    END ".$direction);
            }

            return $query->orderBy($sortBy, $direction);
        }

        // Default sorting by created_at desc
        return $query->orderBy('created_at', 'desc');
    }
}
