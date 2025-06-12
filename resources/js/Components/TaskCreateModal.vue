<script setup lang="ts">
import Modal from '@/Components/Modal.vue';
import { router } from '@inertiajs/vue3';
import { ref, reactive, computed, watch } from 'vue';

interface TaskForm {
    title: string;
    status: 'pending' | 'in-progress' | 'completed';
    priority: 'low' | 'medium' | 'high';
    due_date: string;
}

interface ValidationErrors {
    title?: string[];
    status?: string[];
    priority?: string[];
    due_date?: string[];
}

interface Props {
    show: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
    success: [message: string];
}>();

const form = reactive<TaskForm>({
    title: '',
    status: 'pending',
    priority: 'medium',
    due_date: '',
});

const errors = ref<ValidationErrors>({});
const isSubmitting = ref(false);
const submitAttempted = ref(false);

const statusOptions = [
    { value: 'pending', label: 'Pending' },
    { value: 'in-progress', label: 'In Progress' },
    { value: 'completed', label: 'Completed' },
];

const priorityOptions = [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
];

const clientSideValidation = computed(() => {
    const clientErrors: ValidationErrors = {};

    if (submitAttempted.value || form.title.length > 0) {
        if (!form.title.trim()) {
            clientErrors.title = ['Task title is required.'];
        } else if (form.title.length > 255) {
            clientErrors.title = ['Task title cannot exceed 255 characters.'];
        }
    }

    if (submitAttempted.value) {
        if (!form.status) {
            clientErrors.status = ['Task status is required.'];
        }
        if (!form.priority) {
            clientErrors.priority = ['Task priority is required.'];
        }
        if (!form.due_date) {
            clientErrors.due_date = ['Due date is required.'];
        }
    }

    if (form.due_date && submitAttempted.value) {
        const selectedDate = new Date(form.due_date);
        const today = new Date();
        if (selectedDate <= today) {
            clientErrors.due_date = ['Due date must be after today.'];
        }
    }

    return clientErrors;
});

const hasErrors = computed(() => {
    return Object.keys(clientSideValidation.value).length > 0 || Object.keys(errors.value).length > 0;
});

const getFieldErrors = (field: keyof ValidationErrors) => {
    return errors.value[field] || clientSideValidation.value[field] || [];
};

const hasFieldError = (field: keyof ValidationErrors) => {
    return getFieldErrors(field).length > 0;
};

watch(form, () => {
    if (Object.keys(errors.value).length > 0) {
        errors.value = {};
    }
}, { deep: true });

watch(() => props.show, (newValue) => {
    if (!newValue) {
        resetForm();
    }
});

const submitForm = async () => {
    submitAttempted.value = true;
    
    if (hasErrors.value) {
        return;
    }

    isSubmitting.value = true;
    errors.value = {};

    router.post('/tasks', form, {
        onSuccess: () => {
            emit('success', 'Task created successfully!');
            emit('close');
        },
        onError: (responseErrors) => {
            errors.value = responseErrors;
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

const resetForm = () => {
    Object.assign(form, {
        title: '',
        status: 'pending',
        priority: 'medium',
        due_date: '',
    });
    errors.value = {};
    submitAttempted.value = false;
};

const closeModal = () => {
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="closeModal" max-width="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    Create New Task
                </h2>
                <button
                    @click="closeModal"
                    class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                >
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">
                        Task Title <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input
                            id="title"
                            v-model="form.title"
                            type="text"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{
                                'border-red-300 text-red-900 placeholder-red-300 focus:border-red-500 focus:ring-red-500': hasFieldError('title')
                            }"
                            placeholder="Enter task title"
                        />
                    </div>
                    <div v-if="hasFieldError('title')" class="mt-2 text-sm text-red-600">
                        <p v-for="error in getFieldErrors('title')" :key="error">{{ error }}</p>
                    </div>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <select
                            id="status"
                            v-model="form.status"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{
                                'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500': hasFieldError('status')
                            }"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div v-if="hasFieldError('status')" class="mt-2 text-sm text-red-600">
                        <p v-for="error in getFieldErrors('status')" :key="error">{{ error }}</p>
                    </div>
                </div>

                <div>
                    <label for="priority" class="block text-sm font-medium text-gray-700">
                        Priority <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <select
                            id="priority"
                            v-model="form.priority"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{
                                'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500': hasFieldError('priority')
                            }"
                        >
                            <option v-for="option in priorityOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                    </div>
                    <div v-if="hasFieldError('priority')" class="mt-2 text-sm text-red-600">
                        <p v-for="error in getFieldErrors('priority')" :key="error">{{ error }}</p>
                    </div>
                </div>

                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700">
                        Due Date <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input
                            id="due_date"
                            v-model="form.due_date"
                            type="date"
                            required
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            :class="{
                                'border-red-300 text-red-900 focus:border-red-500 focus:ring-red-500': hasFieldError('due_date')
                            }"
                        />
                    </div>
                    <div v-if="hasFieldError('due_date')" class="mt-2 text-sm text-red-600">
                        <p v-for="error in getFieldErrors('due_date')" :key="error">{{ error }}</p>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button
                        type="button"
                        @click="closeModal"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        :disabled="isSubmitting"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        @click="resetForm"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        :disabled="isSubmitting"
                    >
                        Reset
                    </button>

                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        :disabled="isSubmitting"
                    >
                        <svg v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ isSubmitting ? 'Creating...' : 'Create Task' }}
                    </button>
                </div>
            </form>
        </div>
    </Modal>
</template> 