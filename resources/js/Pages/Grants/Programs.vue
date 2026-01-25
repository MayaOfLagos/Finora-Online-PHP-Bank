<script setup>
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DashboardLayout from '@/Layouts/DashboardLayout.vue';
import Card from 'primevue/card';
import Button from 'primevue/button';
import Tag from 'primevue/tag';
import Message from 'primevue/message';

const props = defineProps({
    programs: {
        type: Array,
        default: () => [],
    },
});

const expanded = ref(new Set());

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(value);
};

const getCriteriaIcon = (criterion) => {
    const icons = {
        'age': 'pi-calendar',
        'income': 'pi-wallet',
        'credit': 'pi-chart-bar',
        'employment': 'pi-briefcase',
        'education': 'pi-book',
        'resident': 'pi-home',
    };
    return icons[criterion.toLowerCase()] || 'pi-check-circle';
};

const applyForGrant = (program) => {
    // TODO: Create apply page
    router.visit(`/grants/${program.id}/apply`);
};

const viewApplications = () => {
    router.visit('/grants/applications');
};

const toggleDetails = (id) => {
    const next = new Set(expanded.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    expanded.value = next;
};

const isExpanded = (id) => expanded.value.has(id);
</script>

<template>
    <DashboardLayout title="Grant Programs">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Available Grants</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">Explore and apply for grants that match your needs</p>
                </div>
                <Button 
                    label="My Applications" 
                    icon="pi pi-file"
                    severity="secondary"
                    @click="viewApplications"
                />
            </div>

            <!-- Programs Grid -->
            <div class="grid gap-6 grid-cols-1 md:grid-cols-2">
                <Card v-for="program in programs" :key="program.id" class="shadow-lg">
                    <template #header>
                        <div class="bg-gradient-to-r from-indigo-500 to-blue-600 p-6 text-white">
                            <div class="flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <h3 class="text-2xl font-bold">{{ program.name }}</h3>
                                    <p class="text-indigo-100 text-sm line-clamp-2">{{ program.description }}</p>
                                    <div class="flex flex-wrap items-center gap-3 text-sm text-indigo-100">
                                        <span class="flex items-center gap-1"><i class="pi pi-calendar"></i> {{ program.end_date || 'Ongoing' }}</span>
                                        <span class="flex items-center gap-1"><i class="pi pi-users"></i> {{ program.applications_count }}/{{ program.max_recipients }} spots</span>
                                    </div>
                                </div>
                                <div class="flex flex-col items-end gap-3">
                                    <Tag :value="formatCurrency(program.amount_display)" severity="success" class="text-base font-semibold" />
                                    <Tag v-if="program.user_has_applied" :value="`Status: ${program.user_application_status}`" severity="info" class="text-xs" />
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #content>
                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Grant Amount</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ formatCurrency(program.amount_display) }}</p>
                                </div>
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Deadline</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ program.end_date || 'Ongoing' }}</p>
                                </div>
                                <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                                    <p class="text-xs text-gray-600 dark:text-gray-400">Applications</p>
                                    <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">{{ program.applications_count }}/{{ program.max_recipients }}</p>
                                </div>
                            </div>

                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                    {{ program.description }}
                                </div>
                                <Button
                                    text
                                    icon-pos="right"
                                    :icon="isExpanded(program.id) ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
                                    label="Details"
                                    class="whitespace-nowrap"
                                    @click="toggleDetails(program.id)"
                                />
                            </div>

                            <div v-if="isExpanded(program.id)" class="space-y-4 border-t border-gray-200 dark:border-gray-700 pt-4">
                                <div v-if="program.eligibility_criteria?.length" class="space-y-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Eligibility Criteria</h4>
                                    <div class="space-y-2">
                                        <div v-for="(criterion, idx) in program.eligibility_criteria" :key="idx" class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <i :class="['pi', getCriteriaIcon(criterion), 'mt-0.5 text-indigo-500 flex-shrink-0']"></i>
                                            <span>{{ criterion }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="program.required_documents?.length" class="space-y-2">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">Required Documents</h4>
                                    <div class="space-y-2">
                                        <div v-for="(doc, idx) in program.required_documents" :key="idx" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                            <i class="pi pi-file text-orange-500"></i>
                                            <span>{{ doc }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #footer>
                        <div class="flex gap-3 flex-wrap">
                            <Button
                                text
                                :icon="isExpanded(program.id) ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"
                                label="Toggle Details"
                                class="md:hidden"
                                @click="toggleDetails(program.id)"
                            />
                            <Button
                                v-if="!program.user_has_applied"
                                label="Apply Now"
                                icon="pi pi-arrow-right"
                                class="flex-1"
                                @click="applyForGrant(program)"
                            />
                            <Button
                                v-else
                                label="View Application"
                                icon="pi pi-eye"
                                severity="secondary"
                                outlined
                                class="flex-1"
                                @click="viewApplications"
                            />
                        </div>
                    </template>
                </Card>
            </div>

            <!-- Empty State -->
            <div v-if="programs.length === 0" class="p-12 text-center bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                <i class="pi pi-inbox text-6xl text-gray-300 dark:text-gray-600 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">No Active Grants</h3>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Check back later for new grant opportunities</p>
            </div>
        </div>
    </DashboardLayout>
</template>
