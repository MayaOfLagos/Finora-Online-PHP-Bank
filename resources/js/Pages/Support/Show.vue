<script setup>
import { ref, computed, nextTick, onMounted } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Textarea from 'primevue/textarea';
import Tag from 'primevue/tag';
import Avatar from 'primevue/avatar';
import Dialog from 'primevue/dialog';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    ticket: Object,
    messages: Array,
});

const toast = useToast();
const page = usePage();
const chatContainer = ref(null);
const showCloseDialog = ref(false);

const currentUser = computed(() => page.props.auth?.user);

const form = useForm({
    message: '',
});

const statusColors = {
    open: 'info',
    in_progress: 'warning',
    waiting: 'secondary',
    resolved: 'success',
    closed: 'secondary',
};

const priorityColors = {
    low: 'success',
    medium: 'info',
    high: 'warning',
    urgent: 'danger',
};

const getStatusSeverity = (status) => {
    return statusColors[status] || 'info';
};

const getPrioritySeverity = (priority) => {
    return priorityColors[priority] || 'info';
};

const scrollToBottom = () => {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
};

onMounted(() => {
    scrollToBottom();
});

const sendReply = () => {
    if (!form.message.trim()) {
        toast.warn('Please type a message before sending.', 'Empty Message');
        return;
    }

    form.post(route('support.reply', props.ticket.uuid), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            scrollToBottom();
            toast.success('Your reply has been sent successfully.', 'Message Sent');
        },
        onError: (errors) => {
            toast.error(Object.values(errors)[0] || 'Failed to send message.', 'Error');
        },
    });
};

const closeTicket = () => {
    router.post(route('support.close', props.ticket.uuid), {}, {
        onSuccess: () => {
            showCloseDialog.value = false;
            toast.success('Your ticket has been closed.', 'Ticket Closed');
        },
        onError: () => {
            toast.error('Failed to close ticket.', 'Error');
        },
    });
};

const goBack = () => {
    router.visit(route('support.index'));
};

const getInitials = (name) => {
    if (!name) return '?';
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
};
</script>

<template>
    <DashboardLayout :title="`Ticket #${ticket.ticket_number}`">
        <div class="space-y-4">
            <!-- Header with Back Button -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <Button 
                        icon="pi pi-arrow-left" 
                        text 
                        rounded
                        @click="goBack"
                        class="flex-shrink-0"
                    />
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                            {{ ticket.subject }}
                        </h1>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <span class="font-mono">{{ ticket.ticket_number }}</span>
                            <span>•</span>
                            <span>{{ ticket.category }}</span>
                            <span>•</span>
                            <span>{{ ticket.created_at }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 pl-10 sm:pl-0">
                    <Tag :value="ticket.status_label" :severity="getStatusSeverity(ticket.status)" />
                    <Tag :value="ticket.priority_label" :severity="getPrioritySeverity(ticket.priority)" class="!bg-opacity-20" />
                    <Button 
                        v-if="ticket.can_reply && ticket.status !== 'closed'"
                        icon="pi pi-times"
                        label="Close Ticket"
                        severity="secondary"
                        outlined
                        size="small"
                        @click="showCloseDialog = true"
                    />
                </div>
            </div>

            <!-- Chat Container -->
            <Card class="!p-0">
                <template #content>
                    <!-- Messages Area -->
                    <div 
                        ref="chatContainer"
                        class="h-[500px] overflow-y-auto p-4 space-y-4 bg-gray-50 dark:bg-gray-900/50"
                    >
                        <div 
                            v-for="message in messages" 
                            :key="message.id"
                            class="flex"
                            :class="message.is_agent ? 'justify-start' : 'justify-end'"
                        >
                            <div 
                                class="max-w-[80%] md:max-w-[70%]"
                                :class="message.is_agent ? 'order-2' : 'order-1'"
                            >
                                <!-- Sender Info -->
                                <div 
                                    class="flex items-center gap-2 mb-1"
                                    :class="message.is_agent ? 'flex-row' : 'flex-row-reverse'"
                                >
                                    <Avatar 
                                        v-if="message.sender_avatar"
                                        :image="message.sender_avatar"
                                        shape="circle"
                                        size="small"
                                    />
                                    <Avatar 
                                        v-else
                                        :label="getInitials(message.sender_name)"
                                        shape="circle"
                                        size="small"
                                        :class="message.is_agent ? 'bg-blue-500 text-white' : 'bg-primary text-white'"
                                    />
                                    <span class="text-xs font-medium text-gray-600 dark:text-gray-400">
                                        {{ message.sender_name }}
                                    </span>
                                    <Tag 
                                        v-if="message.is_agent" 
                                        value="Support" 
                                        severity="info"
                                        class="!text-xs !py-0 !px-1"
                                    />
                                    <Tag 
                                        v-if="message.type === 'system'" 
                                        value="System" 
                                        severity="secondary"
                                        class="!text-xs !py-0 !px-1"
                                    />
                                </div>

                                <!-- Message Bubble -->
                                <div 
                                    class="p-3 rounded-2xl shadow-sm"
                                    :class="[
                                        message.is_agent 
                                            ? 'bg-white dark:bg-gray-800 border dark:border-gray-700 rounded-tl-sm' 
                                            : 'bg-primary text-white rounded-tr-sm',
                                        message.type === 'system' && '!bg-gray-200 dark:!bg-gray-700 !text-gray-600 dark:!text-gray-400 italic'
                                    ]"
                                >
                                    <p class=" text-black dark:text-white">{{ message.message }}</p>
                                </div>

                                <!-- Timestamp -->
                                <div 
                                    class="text-xs text-gray-400 dark:text-gray-500 mt-1 px-1"
                                    :class="message.is_agent ? 'text-left' : 'text-right'"
                                >
                                    {{ message.created_at }}
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!messages || messages.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                            <i class="pi pi-comments text-5xl mb-3"></i>
                            <p>No messages yet</p>
                        </div>
                    </div>

                    <!-- Reply Input (only if ticket is open) -->
                    <div v-if="ticket.can_reply" class="border-t dark:border-gray-700 p-4 bg-white dark:bg-gray-800">
                        <form @submit.prevent="sendReply" class="flex gap-3">
                            <div class="flex-1">
                                <Textarea 
                                    v-model="form.message"
                                    placeholder="Type your message..."
                                    rows="2"
                                    class="w-full resize-none"
                                    :class="{ 'p-invalid': form.errors.message }"
                                    @keydown.ctrl.enter="sendReply"
                                    @keydown.meta.enter="sendReply"
                                />
                                <small class="text-gray-400 dark:text-gray-500 text-xs mt-1 block">
                                    Press Ctrl+Enter to send
                                </small>
                            </div>
                            <div class="flex flex-col justify-start">
                                <Button 
                                    type="submit"
                                    icon="pi pi-send"
                                    label="Send"
                                    :loading="form.processing"
                                    :disabled="!form.message.trim()"
                                />
                            </div>
                        </form>
                    </div>

                    <!-- Closed Ticket Message -->
                    <div v-else class="border-t dark:border-gray-700 p-4 bg-gray-100 dark:bg-gray-800 text-center">
                        <p class="text-gray-500 dark:text-gray-400">
                            <i class="pi pi-lock mr-2"></i>
                            This ticket is {{ ticket.status_label.toLowerCase() }}. You cannot reply.
                        </p>
                        <Button 
                            label="Open New Ticket" 
                            text 
                            class="mt-2"
                            @click="goBack"
                        />
                    </div>
                </template>
            </Card>

            <!-- Ticket Info Card -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2 text-base">
                        <i class="pi pi-info-circle text-primary"></i>
                        Ticket Information
                    </div>
                </template>
                <template #content>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Ticket Number</p>
                            <p class="font-mono font-semibold text-gray-900 dark:text-white">{{ ticket.ticket_number }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Status</p>
                            <Tag :value="ticket.status_label" :severity="getStatusSeverity(ticket.status)" />
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Priority</p>
                            <Tag :value="ticket.priority_label" :severity="getPrioritySeverity(ticket.priority)" />
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Category</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ ticket.category }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Created</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ ticket.created_at }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Last Updated</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ ticket.updated_at }}</p>
                        </div>
                        <div v-if="ticket.assigned_agent">
                            <p class="text-gray-500 dark:text-gray-400">Assigned Agent</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ ticket.assigned_agent }}</p>
                        </div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Close Ticket Confirmation Dialog -->
        <Dialog 
            v-model:visible="showCloseDialog" 
            header="Close Ticket" 
            :modal="true" 
            :closable="true"
            :style="{ width: '400px' }"
        >
            <div class="flex items-center gap-3">
                <i class="pi pi-exclamation-triangle text-yellow-500 text-3xl"></i>
                <p class="text-gray-700 dark:text-gray-300">
                    Are you sure you want to close this ticket? You won't be able to reply to it anymore.
                </p>
            </div>
            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button 
                        label="Cancel" 
                        severity="secondary"
                        outlined
                        @click="showCloseDialog = false"
                    />
                    <Button 
                        label="Close Ticket" 
                        severity="danger"
                        @click="closeTicket"
                    />
                </div>
            </template>
        </Dialog>
    </DashboardLayout>
</template>

<style scoped>
/* Custom scrollbar for chat */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(156, 163, 175, 0.7);
}
</style>
