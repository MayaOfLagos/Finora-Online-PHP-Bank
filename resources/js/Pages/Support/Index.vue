<script setup>
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';

const faqs = ref([
    { q: 'How do I reset my PIN?', a: 'Go to Security settings and follow the PIN reset flow.' },
    { q: 'Where can I see transfer limits?', a: 'Open Accounts, select an account, and view Limits.' },
]);

const tickets = ref([
    { id: 'TK-1042', subject: 'Card not working online', status: 'open', updated_at: '2h ago' },
    { id: 'TK-1036', subject: 'Need statement for visa', status: 'closed', updated_at: '1d ago' },
]);

const ticketStatus = {
    open: { label: 'Open', severity: 'info' },
    pending: { label: 'Pending', severity: 'warning' },
    closed: { label: 'Closed', severity: 'secondary' },
};
</script>

<template>
    <DashboardLayout title="Support">
        <div class="space-y-6">
            <div class="flex flex-col gap-2">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Support</h1>
                <p class="text-gray-600 dark:text-gray-400">Get help, browse FAQs, or open a ticket.</p>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-2">
                    <template #title>Open a Ticket</template>
                    <template #content>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Subject</label>
                                <InputText placeholder="How can we help?" class="w-full mt-1" />
                            </div>
                            <div class="md:col-span-2">
                                <label class="text-sm text-gray-700 dark:text-gray-300">Details</label>
                                <Textarea rows="4" placeholder="Describe your issue" class="w-full mt-1" />
                            </div>
                            <div class="md:col-span-2 flex justify-end">
                                <Button label="Submit" icon="pi pi-send" />
                            </div>
                        </div>
                        <Message severity="secondary" :closable="false" class="mt-3">Live ticket sync coming soon.</Message>
                    </template>
                </Card>

                <Card>
                    <template #title>Quick Answers</template>
                    <template #content>
                        <div class="space-y-3">
                            <div v-for="item in faqs" :key="item.q" class="p-3 border rounded-lg dark:border-gray-700">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ item.q }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ item.a }}</p>
                            </div>
                            <p v-if="!faqs.length" class="text-sm text-gray-600 dark:text-gray-400">No FAQs yet.</p>
                        </div>
                    </template>
                </Card>
            </div>

            <Card>
                <template #title>My Tickets</template>
                <template #content>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        <div v-for="ticket in tickets" :key="ticket.id" class="p-4 border rounded-xl dark:border-gray-700 bg-white dark:bg-gray-800 space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="font-semibold text-gray-900 dark:text-white">{{ ticket.subject }}</p>
                                <Tag :value="ticketStatus[ticket.status]?.label || ticket.status" :severity="ticketStatus[ticket.status]?.severity || 'info'" />
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Ref: {{ ticket.id }}</p>
                            <p class="text-xs text-gray-500">Updated {{ ticket.updated_at }}</p>
                        </div>
                    </div>
                    <p v-if="!tickets.length" class="text-sm text-gray-600 dark:text-gray-400">No tickets yet.</p>
                </template>
            </Card>
        </div>
    </DashboardLayout>
</template>
