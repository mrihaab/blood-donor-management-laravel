<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: true,
});

const quickLogin = (email, password) => {
    form.email = email;
    form.password = password;
    submit();
};

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Clinical Portal Login" />

        <!-- Clinical Header Branding -->
        <div class="mb-6 text-center">
            <div class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-tr from-red-600 to-rose-500 text-white font-black text-xl shadow-lg shadow-red-500/20 mb-3">
                💉
            </div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Clinical Operations Portal</h2>
            <p class="text-xs font-semibold text-slate-500 mt-1">Hospital Attendants, Clinicians & Command Center Staff</p>
        </div>

        <div v-if="status" class="mb-4 font-medium text-xs text-green-600 bg-green-50 p-3 rounded-xl border border-green-200">
            {{ status }}
        </div>

        <!-- 1-Click Quick Demo Login Preset Buttons for Mobile Testing -->
        <div class="mb-6 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80 space-y-2">
            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500 text-center">⚡ 1-Click Mobile Testing Login</p>
            <div class="grid grid-cols-2 gap-2">
                <button 
                    type="button" 
                    @click="quickLogin('hospital@rihaab.com', 'password')"
                    class="py-2.5 px-3 bg-blue-600 hover:bg-blue-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md shadow-blue-500/20 flex flex-col items-center justify-center transition"
                >
                    <span>🏥 Hospital Doctor</span>
                    <span class="text-[9px] font-normal opacity-80">hospital@rihaab.com</span>
                </button>
                <button 
                    type="button" 
                    @click="quickLogin('admin@bloodbank.com', 'password')"
                    class="py-2.5 px-3 bg-rose-600 hover:bg-rose-700 active:scale-95 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-500/20 flex flex-col items-center justify-center transition"
                >
                    <span>🛡️ Admin Portal</span>
                    <span class="text-[9px] font-normal opacity-80">admin@bloodbank.com</span>
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Clinical Staff Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full text-sm font-medium rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="doctor@hospital.com"
                />

                <InputError class="mt-1 text-xs" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full text-sm font-medium rounded-xl border-slate-200 focus:border-red-500 focus:ring-red-500"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-1 text-xs" :message="form.errors.password" />
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-xs font-medium text-slate-600">Keep session active</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs font-semibold text-slate-500 hover:text-red-600"
                >
                    Forgot?
                </Link>
            </div>

            <div class="pt-2">
                <PrimaryButton class="w-full justify-center py-3 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 active:scale-95 text-white font-bold rounded-xl text-sm shadow-lg shadow-red-500/25 transition" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Sign In to Clinical System &rarr;
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
