<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    totalDonors: Number,
    totalRequests: Number,
    availableUnits: Array,
    donationsChart: Array,
    recentActivities: Array,
});

// Calculate total blood units
const totalBloodUnits = computed(() => {
    return props.availableUnits.reduce((sum, unit) => sum + unit.total, 0);
});
</script>

<template>
    <Head title="Admin Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Admin Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Total Donors -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Donors</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ totalDonors }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Requests -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-red-100 text-red-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Requests</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ totalRequests }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Blood Units -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 text-green-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Blood Units</p>
                                    <p class="text-3xl font-bold text-gray-900">{{ totalBloodUnits }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                            <div class="space-y-2">
                                <button class="w-full text-left px-3 py-2 text-sm text-blue-600 hover:bg-blue-50 rounded">
                                    Add Donor
                                </button>
                                <button class="w-full text-left px-3 py-2 text-sm text-green-600 hover:bg-green-50 rounded">
                                    Record Donation
                                </button>
                                <button class="w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                                    View Requests
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Blood Inventory -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Blood Inventory</h3>
                            <div class="space-y-3">
                                <div v-for="unit in availableUnits" :key="unit.blood_group" class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-gray-700">{{ unit.blood_group }}</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="unit.total > 10 ? 'bg-green-100 text-green-800' : 
                                                 unit.total > 5 ? 'bg-yellow-100 text-yellow-800' : 
                                                 'bg-red-100 text-red-800'">
                                        {{ unit.total }} units
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activities</h3>
                            <div class="space-y-3">
                                <div v-for="activity in recentActivities" :key="activity.id" 
                                     class="flex items-start space-x-3">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                        <p class="text-xs text-gray-500">{{ new Date(activity.created_at).toLocaleDateString() }}</p>
                                    </div>
                                </div>
                                <div v-if="recentActivities.length === 0" class="text-sm text-gray-500 text-center py-4">
                                    No recent activities
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
