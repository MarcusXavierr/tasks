<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TaskFilters from '@/Components/TaskFilters.vue';
import SortableTableHeader from '@/Components/SortableTableHeader.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useTaskStore } from '@/stores/taskStore';
import { onMounted } from 'vue';

interface Task {
    id: number;
    title: string;
    status: 'pending' | 'in-progress' | 'completed';
    priority: 'low' | 'medium' | 'high';
    due_date: string;
    created_at?: string;
    updated_at?: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedTasks {
    data: Task[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

interface Filters {
    status: string | null;
    priority: string | null;
    sort_by: string | null;
    sort_direction: 'asc' | 'desc';
    per_page: number;
}

interface Props {
    tasks: PaginatedTasks;
    filters: Filters;
}

const props = defineProps<Props>();
const taskStore = useTaskStore();

// Initialize store with server filters on component mount
onMounted(() => {
    taskStore.initializeFilters(props.filters);
});

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString();
};

const getStatusBadgeClass = (status: Task['status']): string => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-semibold';
    switch (status) {
        case 'completed':
            return `${baseClasses} bg-green-100 text-green-800`;
        case 'in-progress':
            return `${baseClasses} bg-yellow-100 text-yellow-800`;
        case 'pending':
            return `${baseClasses} bg-gray-100 text-gray-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};

const getPriorityBadgeClass = (priority: Task['priority']): string => {
    const baseClasses = 'px-2 py-1 rounded-full text-xs font-semibold';
    switch (priority) {
        case 'high':
            return `${baseClasses} bg-red-100 text-red-800`;
        case 'medium':
            return `${baseClasses} bg-blue-100 text-blue-800`;
        case 'low':
            return `${baseClasses} bg-green-100 text-green-800`;
        default:
            return `${baseClasses} bg-gray-100 text-gray-800`;
    }
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tasks Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Filters -->
                <TaskFilters />

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Task Count and Pagination Info -->
                        <div v-if="tasks.data && tasks.data.length > 0" class="mb-4">
                            <p class="text-sm text-gray-700">
                                Showing 
                                <span class="font-medium">{{ tasks.from }}</span>
                                to 
                                <span class="font-medium">{{ tasks.to }}</span>
                                of 
                                <span class="font-medium">{{ tasks.total }}</span>
                                results
                            </p>
                        </div>

                        <!-- Tasks Table -->
                        <div v-if="tasks.data && tasks.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Title
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <SortableTableHeader column="priority" label="Priority" />
                                        <SortableTableHeader column="due_date" label="Due Date" />
                                        <SortableTableHeader column="created_at" label="Created" />
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ task.title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusBadgeClass(task.status)">
                                                {{ task.status.charAt(0).toUpperCase() + task.status.slice(1).replace('-', ' ') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getPriorityBadgeClass(task.priority)">
                                                {{ task.priority.charAt(0).toUpperCase() + task.priority.slice(1) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(task.due_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ task.created_at ? formatDate(task.created_at) : 'N/A' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-8">
                            <p class="text-gray-500">No tasks found. Create some tasks to get started!</p>
                        </div>

                        <!-- Pagination -->
                        <div v-if="tasks.data && tasks.data.length > 0 && tasks.last_page > 1" class="mt-6">
                            <nav class="flex items-center justify-between border-t border-gray-200 px-4 sm:px-0">
                                <div class="-mt-px flex w-0 flex-1">
                                    <Link
                                        v-if="tasks.links && tasks.links[0] && tasks.links[0].url"
                                        :href="tasks.links[0].url"
                                        class="inline-flex items-center border-t-2 border-transparent pr-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                                        preserve-state
                                        preserve-scroll
                                    >
                                        <svg class="mr-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M18 10a.75.75 0 01-.75.75H4.66l2.1 1.95a.75.75 0 11-1.02 1.1l-3.5-3.25a.75.75 0 010-1.1l3.5-3.25a.75.75 0 111.02 1.1L4.66 9.25H17.25A.75.75 0 0118 10z" clip-rule="evenodd" />
                                        </svg>
                                        Previous
                                    </Link>
                                </div>
                                <div class="hidden md:-mt-px md:flex">
                                    <template v-for="(link, index) in tasks.links" :key="index">
                                        <Link
                                            v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                            :href="link.url"
                                            :class="[
                                                link.active
                                                    ? 'border-indigo-500 text-indigo-600 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium'
                                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium'
                                            ]"
                                            preserve-state
                                            preserve-scroll
                                        >
                                            {{ link.label }}
                                        </Link>
                                        <span
                                            v-else-if="!link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                            class="border-transparent text-gray-500 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium"
                                        >
                                            {{ link.label }}
                                        </span>
                                    </template>
                                </div>
                                <div class="-mt-px flex w-0 flex-1 justify-end">
                                    <Link
                                        v-if="tasks.links && tasks.links[tasks.links.length - 1] && tasks.links[tasks.links.length - 1].url"
                                        :href="tasks.links[tasks.links.length - 1].url!"
                                        class="inline-flex items-center border-t-2 border-transparent pl-1 pt-4 text-sm font-medium text-gray-500 hover:border-gray-300 hover:text-gray-700"
                                        preserve-state
                                        preserve-scroll
                                    >
                                        Next
                                        <svg class="ml-3 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z" clip-rule="evenodd" />
                                        </svg>
                                    </Link>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
