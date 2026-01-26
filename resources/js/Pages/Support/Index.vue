<script setup>
import { ref, computed } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    tickets: Array,
    categories: Array,
    priorities: Array,
    faqs: Array,
});

const toast = useToast();
const page = usePage();
const showNewTicketDialog = ref(false);

const form = useForm({
    subject: '',
    message: '',
    category_id: null,
    priority: 'medium',
});

const ticketStatusColors = {
    open: 'info',
    in_progress: 'warning',
    waiting: 'secondary',
    resolved: 'success',
    closed: 'secondary',
};

const priorityOptions = computed(() => props.priorities || [
    { value: 'low', label: 'Low' },
    { value: 'medium', label: 'Medium' },
    { value: 'high', label: 'High' },
    { value: 'urgent', label: 'Urgent' },
]);

const openNewTicketDialog = () => {
    form.reset();
    showNewTicketDialog.value = true;
};

const submitTicket = () => {
    form.post(route('support.store'), {
        onSuccess: () => {
            showNewTicketDialog.value = false;
            toast.add({
                severity: 'success',
                summary: 'Ticket Created',
                detail: 'Your support ticket has been submitted successfully.',
                life: 5000,
            });
        },
        onError: (errors) => {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: Object.values(errors)[0] || 'Failed to create ticket.',
                life: 5000,
            });
        },
    });
};

const viewTicket = (ticket) => {
    router.visit(route('support.show', ticket.uuid));
};

const getStatusSeverity = (status) => {
    return ticketStatusColors[status] || 'info';
};
</script>

<template>
    <DashboardLayout title="Support">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support</h1>
                    <p class="text-gray-600 dark:text-gray-400">Get help, browse FAQs, or open a ticket.</p>
                </div>
                <Button 
                    label="New Ticket" 
                    icon="pi pi-plus" 
                    @click="openNewTicketDialog"
                    class="w-full sm:w-auto"
                />
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- My Tickets -->
                <Card class="lg:col-span-2">
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-ticket text-primary"></i>
                            My Tickets
                        </div>
                    </template>
                    <template #content>
                        <div v-if="tickets && tickets.length" class="space-y-3">
                            <div 
                                v-for="ticket in tickets" 
                                :key="ticket.uuid" 
                                @click="viewTicket(ticket)"
                                class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-750 transition-colors"
                            >
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="font-semibold text-gray-900 dark:text-white truncate">
                                                {{ ticket.subject }}
                                            </h3>
                                            <span v-if="ticket.unread_count" class="flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">
                                                {{ ticket.unread_count }}
                                            </span>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span class="font-mono">{{ ticket.ticket_number }}</span>
                                            <span>•</span>
                                            <span>{{ ticket.category }}</span>
                                            <span>•</span>
                                            <span>{{ ticket.updated_at }}</span>
                                        </div>
                                        <p v-if="ticket.last_message" class="mt-2 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                                            {{ ticket.last_message }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end gap-2">
                                        <Tag :value="ticket.status_label" :severity="getStatusSeverity(ticket.status)" />
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ ticket.priority_label }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <i class="pi pi-inbox text-4xl text-gray-300 dark:text-gray-600 mb-3"></i>
                            <p class="text-gray-500 dark:text-gray-400">No tickets yet</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Click "New Ticket" to get started</p>
                        </div>
                    </template>
                </Card>

                <!-- Quick Answers / FAQs -->
                <Card>
                    <template #title>
                        <div class="flex items-center gap-2">
                            <i class="pi pi-question-circle text-primary"></i>
                            Quick Answers
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-3">
                            <div 
                                v-for="(item, index) in faqs" 
                                :key="index" 
                                class="p-3 border rounded-lg dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50"
                            >
                                <p class="font-semibold text-gray-900 dark:text-white text-sm">
                                    <i class="pi pi-question-circle mr-1 text-primary"></i>
                                    {{ item.question }}
                                </p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 pl-5">
                                    {{ item.answer }}
                                </p>
                            </div>
                            <p v-if="!faqs || !faqs.length" class="text-sm text-gray-600 dark:text-gray-400 text-center py-4">
                                No FAQs available.
                            </p>
                        </div>
                    </template>
                </Card>
            </div>
        </div>

        <!-- New Ticket Dialog -->
        <Dialog 
            v-model:visible="showNewTicketDialog" 
            header="Open New Ticket" 
            :modal="true" 
            :closable="true"
            :style="{ width: '500px' }"
            class="p-fluid"
        >
            <form @submit.prevent="submitTicket" class="space-y-4 pt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category *</label>
                    <Select 
                        v-model="form.category_id"
                        :options="categories"
                        optionLabel="name"
                        optionValue="id"
                        placeholder="Select a category"
                        class="w-full"
                        :class="{ 'p-invalid': form.errors.category_id }"
                    />
                    <small v-if="form.errors.category_id" class="text-red-500">{{ form.errors.category_id }}</small>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject *</label>
                    <InputText 
                        v-model="form.subject"
                        placeholder="Brief summary of your issue"
                        class="w-full"
                        :class="{ 'p-invalid': form.errors.subject }"
                    />
                    <small v-if="form.errors.subject" class="text-red-500">{{ form.errors.subject }}</small>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Priority</label>
                    <Select 
                        v-model="form.priority"
                        :options="priorityOptions"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Select priority"
                        class="w-full"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message *</label>
                    <Textarea 
                        v-model="form.message"
                        rows="5"
                        placeholder="Describe your issue in detail..."
                        class="w-full"
                        :class="{ 'p-invalid': form.errors.message }"
                    />
                    <small v-if="form.errors.message" class="text-red-500">{{ form.errors.message }}</small>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button 
                        type="button"
                        label="Cancel" 
                        severity="secondary"
                        outlined
                        @click="showNewTicketDialog = false"
                    />
                    <Button 
                        type="submit"
                        label="Submit Ticket" 
                        icon="pi pi-send"
                        :loading="form.processing"
                    />
                </div>
            </form>
        </Dialog>
    </DashboardLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
