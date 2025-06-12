<script setup lang="ts">
import { useTaskStore } from '@/stores/taskStore'

const taskStore = useTaskStore()

const statusOptions = [
    { value: null, label: 'All Statuses' },
    { value: 'pending', label: 'Pending' },
    { value: 'in-progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' }
]

const priorityOptions = [
    { value: null, label: 'All Priorities' },
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' }
]

const perPageOptions = [
    { value: 5, label: '5 per page' },
    { value: 10, label: '10 per page' },
    { value: 25, label: '25 per page' },
    { value: 50, label: '50 per page' }
]

const handleStatusChange = (event: Event) => {
    const target = event.target as HTMLSelectElement
    taskStore.setStatusFilter(target.value || null)
}

const handlePriorityChange = (event: Event) => {
    const target = event.target as HTMLSelectElement
    taskStore.setPriorityFilter(target.value || null)
}

const handlePerPageChange = (event: Event) => {
    const target = event.target as HTMLSelectElement
    taskStore.setPerPage(parseInt(target.value))
}
</script>

<template>
    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                <!-- Status Filter -->
                <div class="min-w-[140px]">
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">
                        Status
                    </label>
                    <select
                        id="status-filter"
                        :value="taskStore.filters.status"
                        @change="handleStatusChange"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option
                            v-for="option in statusOptions"
                            :key="option.value || 'all'"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div class="min-w-[140px]">
                    <label for="priority-filter" class="block text-sm font-medium text-gray-700 mb-1">
                        Priority
                    </label>
                    <select
                        id="priority-filter"
                        :value="taskStore.filters.priority"
                        @change="handlePriorityChange"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option
                            v-for="option in priorityOptions"
                            :key="option.value || 'all'"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>

                <!-- Per Page Filter -->
                <div class="min-w-[140px]">
                    <label for="per-page-filter" class="block text-sm font-medium text-gray-700 mb-1">
                        Show
                    </label>
                    <select
                        id="per-page-filter"
                        :value="taskStore.filters.per_page"
                        @change="handlePerPageChange"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    >
                        <option
                            v-for="option in perPageOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Clear Filters Button -->
            <div class="flex items-end">
                <button
                    v-if="taskStore.hasActiveFilters"
                    @click="taskStore.clearFilters()"
                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </div>
</template> 