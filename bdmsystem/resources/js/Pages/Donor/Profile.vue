<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object,
    donor: Object,
    bloodGroups: Array,
});

const form = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    blood_group_id: props.donor?.blood_group_id || '',
    gender: props.donor?.gender || '',
    date_of_birth: props.donor?.date_of_birth || '',
    contact_number: props.donor?.contact_number || '',
    address: props.donor?.address || '',
    city: props.donor?.city || '',
    state: props.donor?.state || '',
    zip_code: props.donor?.zip_code || '',
    health_info: props.donor?.health_info || '',
    is_available: props.donor?.is_available ?? true,
});

const submit = () => {
    form.patch(route('donor.profile.update'));
};
</script>

<template>
    <Head title="Donor Profile" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Edit Donor Profile
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="name" value="Full Name" />
                                    <TextInput
                                        id="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.name"
                                        required
                                        autofocus
                                    />
                                    <InputError class="mt-2" :message="form.errors.name" />
                                </div>

                                <div>
                                    <InputLabel for="email" value="Email" />
                                    <TextInput
                                        id="email"
                                        type="email"
                                        class="mt-1 block w-full"
                                        v-model="form.email"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.email" />
                                </div>

                                <div>
                                    <InputLabel for="blood_group_id" value="Blood Group" />
                                    <select
                                        id="blood_group_id"
                                        v-model="form.blood_group_id"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="">Select Blood Group</option>
                                        <option v-for="bg in bloodGroups" :key="bg.id" :value="bg.id">
                                            {{ bg.name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.blood_group_id" />
                                </div>

                                <div>
                                    <InputLabel for="gender" value="Gender" />
                                    <select
                                        id="gender"
                                        v-model="form.gender"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="">Select Gender</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.gender" />
                                </div>

                                <div>
                                    <InputLabel for="date_of_birth" value="Date of Birth" />
                                    <TextInput
                                        id="date_of_birth"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.date_of_birth"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.date_of_birth" />
                                </div>

                                <div>
                                    <InputLabel for="contact_number" value="Contact Number" />
                                    <TextInput
                                        id="contact_number"
                                        type="tel"
                                        class="mt-1 block w-full"
                                        v-model="form.contact_number"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.contact_number" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Address Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <InputLabel for="address" value="Address" />
                                    <textarea
                                        id="address"
                                        v-model="form.address"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="3"
                                        required
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.address" />
                                </div>

                                <div>
                                    <InputLabel for="city" value="City" />
                                    <TextInput
                                        id="city"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.city"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.city" />
                                </div>

                                <div>
                                    <InputLabel for="state" value="State" />
                                    <TextInput
                                        id="state"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.state"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.state" />
                                </div>

                                <div>
                                    <InputLabel for="zip_code" value="ZIP Code" />
                                    <TextInput
                                        id="zip_code"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.zip_code"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.zip_code" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Health Information -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Health Information</h3>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="health_info" value="Health Information (Optional)" />
                                    <textarea
                                        id="health_info"
                                        v-model="form.health_info"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        rows="3"
                                        placeholder="Any relevant health information, medications, or conditions..."
                                    ></textarea>
                                    <InputError class="mt-2" :message="form.errors.health_info" />
                                </div>

                                <div class="flex items-center">
                                    <input
                                        id="is_available"
                                        v-model="form.is_available"
                                        type="checkbox"
                                        class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
                                    />
                                    <label for="is_available" class="ml-2 text-sm text-gray-600">
                                        I am available for blood donation
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            Update Profile
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
