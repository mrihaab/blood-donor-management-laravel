<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Selector</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                    <p class="text-lg font-medium mb-4">Select your portal role to continue:</p>
                    <div class="flex gap-4">
                        <a href="/admin/dashboard" class="px-5 py-3 bg-red-600 text-white rounded-md font-semibold hover:bg-red-700">Admin Portal</a>
                        <a href="/donor/dashboard" class="px-5 py-3 bg-blue-600 text-white rounded-md font-semibold hover:bg-blue-700">Donor Portal</a>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
