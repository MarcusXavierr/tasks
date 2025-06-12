<script setup lang="ts">
import { useTaskStore } from '@/stores/taskStore'

interface Props {
    column: string
    label: string
}

defineProps<Props>()

const taskStore = useTaskStore()

const handleSort = (column: string) => {
    taskStore.setSorting(column)
}

const getSortIcon = (column: string): string => {
    if (taskStore.filters.sort_by !== column) {
        return '↕️' // Both arrows when not sorted by this column
    }
    
    if (taskStore.filters.sort_direction === 'asc') {
        return '↑' // Up arrow for ascending
    } else {
        return '↓' // Down arrow for descending
    }
}

const getSortClasses = (column: string): string => {
    const baseClasses = 'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100 select-none'
    
    if (taskStore.filters.sort_by === column) {
        return `${baseClasses} bg-gray-100`
    }
    
    return baseClasses
}
</script>

<template>
    <th 
        :class="getSortClasses(column)"
        @click="handleSort(column)"
    >
        <div class="flex items-center space-x-1">
            <span>{{ label }}</span>
            <span class="text-gray-400 text-sm">{{ getSortIcon(column) }}</span>
        </div>
    </th>
</template> 