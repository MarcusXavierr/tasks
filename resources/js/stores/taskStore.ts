import { defineStore } from 'pinia'
import { router } from '@inertiajs/vue3'

interface TaskFilters {
    status: string | null
    priority: string | null
    sort_by: string | null
    sort_direction: 'asc' | 'desc'
    per_page: number
}

interface ServerFilters {
    status?: string | null
    priority?: string | null
    sort_by?: string | null
    sort_direction?: 'asc' | 'desc'
    per_page?: number
}

export const useTaskStore = defineStore('task', {
    state: (): { filters: TaskFilters } => ({
        filters: {
            status: null,
            priority: null,
            sort_by: null,
            sort_direction: 'asc',
            per_page: 10,
        }
    }),

    getters: {
        /**
         * Get current filters for API calls
         */
        currentFilters(state): Record<string, string | number> {
            // Filter out null/empty values
            const filtered: Record<string, string | number> = {}
            Object.entries(state.filters).forEach(([key, value]) => {
                if (value !== null && value !== '') {
                    filtered[key] = value
                }
            })
            return filtered
        },

        /**
         * Check if any filters are currently active
         */
        hasActiveFilters(state): boolean {
            return state.filters.status !== null || 
                   state.filters.priority !== null ||
                   state.filters.sort_by !== null
        }
    },

    actions: {
        /**
         * Initialize filters from server props
         */
        initializeFilters(serverFilters: ServerFilters) {
            this.filters = {
                status: serverFilters.status || null,
                priority: serverFilters.priority || null,
                sort_by: serverFilters.sort_by || null,
                sort_direction: serverFilters.sort_direction || 'asc',
                per_page: serverFilters.per_page || 10,
            }
        },

        /**
         * Update status filter and reload data
         */
        setStatusFilter(status: string | null) {
            this.filters.status = status
            this.applyFilters()
        },

        /**
         * Update priority filter and reload data
         */
        setPriorityFilter(priority: string | null) {
            this.filters.priority = priority
            this.applyFilters()
        },

        /**
         * Update sorting and reload data
         */
        setSorting(sortBy: string, direction?: 'asc' | 'desc') {
            // Toggle direction if clicking the same column
            if (this.filters.sort_by === sortBy && direction === undefined) {
                this.filters.sort_direction = this.filters.sort_direction === 'asc' ? 'desc' : 'asc'
            } else {
                this.filters.sort_by = sortBy
                this.filters.sort_direction = direction || 'asc'
            }
            this.applyFilters()
        },

        /**
         * Update per page value and reload data
         */
        setPerPage(perPage: number) {
            this.filters.per_page = perPage
            this.applyFilters()
        },

        /**
         * Clear all filters
         */
        clearFilters() {
            this.filters = {
                status: null,
                priority: null,
                sort_by: null,
                sort_direction: 'asc',
                per_page: 10,
            }
            this.applyFilters()
        },

        /**
         * Apply current filters by navigating to the route with query params
         */
        applyFilters() {
            router.get(route('dashboard'), this.currentFilters, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            })
        }
    }
}) 