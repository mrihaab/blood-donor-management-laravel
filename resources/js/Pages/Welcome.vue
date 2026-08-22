<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome to LifeBlood Management" />

    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
            <Link
                v-if="$page.props.auth && $page.props.auth.user"
                :href="route('dashboard')"
                class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
            >Dashboard</Link>
            <template v-else>
                <Link
                    :href="route('login')"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >Log in</Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="ml-4 font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >Register</Link>
            </template>
        </div>

        <div class="max-w-7xl mx-auto p-6 lg:p-8">
            <div class="flex justify-center">
                <div class="flex items-center space-x-3">
                    <div class="p-3 bg-red-600 rounded-full text-white">
                        <svg class="w-12 h-12 fill-current" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                        </svg>
                    </div>
                    <span class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">LifeBlood Management</span>
                </div>
            </div>

            <div class="mt-12 text-center max-w-2xl mx-auto">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                    Streamlining Blood Donation & Inventory Management
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    A comprehensive platform connecting blood donors, healthcare providers, and administrators to ensure lifesaving blood availability.
                </p>
                <div class="mt-8 flex justify-center gap-4">
                    <Link
                        :href="route('register')"
                        class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg shadow-md hover:bg-red-700 transition duration-150"
                    >Become a Donor</Link>
                    <Link
                        :href="route('login')"
                        class="px-6 py-3 bg-white text-gray-800 font-medium rounded-lg shadow border border-gray-300 hover:bg-gray-50 transition duration-150"
                    >Sign In</Link>
                </div>
            </div>

            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🩸</div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Donor Registration</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Register as a donor, track donation history, and schedule appointments effortlessly.</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">📊</div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Real-Time Inventory</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Monitor blood group stock levels with automated low-stock warnings.</p>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🚨</div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Emergency Requests</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Submit emergency blood requests, assign available donors, and track dispatch status.</p>
                </div>
            </div>
        </div>
    </div>
</template>
