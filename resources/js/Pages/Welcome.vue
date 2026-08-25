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
        <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10 flex items-center gap-4">
            <template v-if="$page.props.auth && $page.props.auth.user">
                <Link
                    :href="route('dashboard')"
                    class="font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >Go to Dashboard &rarr;</Link>
            </template>
            <template v-else>
                <Link
                    :href="route('login')"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >Sign In</Link>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="font-semibold text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                >Register as Donor</Link>
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
                    <span class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">LifeBlood Platform</span>
                </div>
            </div>

            <div class="mt-12 text-center max-w-3xl mx-auto">
                <span class="px-4 py-1.5 rounded-full bg-red-100 text-red-700 text-xs font-bold uppercase tracking-wider">Unified Healthcare Operations</span>
                <h1 class="mt-4 text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
                    Lifesaving Blood Management for Donors, Hospitals & Blood Banks
                </h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                    Connecting blood donors, hospital clinicians, and blood bank managers with real-time FEFO inventory, emergency dispatches, and clinical transfusion traceability.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-4">
                    <template v-if="$page.props.auth && $page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg shadow-md hover:bg-red-700 transition duration-150"
                        >Go to My Dashboard &rarr;</Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="px-6 py-3 bg-red-600 text-white font-medium rounded-lg shadow-md hover:bg-red-700 transition duration-150"
                        >Become a Donor</Link>
                        <Link
                            :href="route('login')"
                            class="px-6 py-3 bg-white text-gray-800 font-medium rounded-lg shadow border border-gray-300 hover:bg-gray-50 transition duration-150"
                        >Sign In (All Roles)</Link>
                    </template>
                </div>
            </div>

            <!-- Portal Guidance Cards -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Donor Portal Card -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🩸</div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Donor Portal</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Register as a blood donor, check your 56-day recovery eligibility, and book appointment visits.</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link :href="route('register')" class="text-sm font-semibold text-red-600 hover:underline">Register as Donor &rarr;</Link>
                    </div>
                </div>

                <!-- Hospital Portal Card -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🏥</div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Hospital Portal</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Authorized hospital staff sign in to register patients, create blood requisitions, and manage clinical transfusions.</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link :href="route('login')" class="text-sm font-semibold text-blue-600 hover:underline">Hospital Staff Sign In &rarr;</Link>
                    </div>
                </div>

                <!-- Patient & Emergency Card -->
                <div class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center font-bold text-xl mb-4">🚨</div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Patient & Emergency Requisitions</h3>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Patient blood units are requisitioned by partner hospitals with server-side ABO/Rh crossmatch verification.</p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <Link :href="route('login')" class="text-sm font-semibold text-amber-600 hover:underline">Request Emergency Blood &rarr;</Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
