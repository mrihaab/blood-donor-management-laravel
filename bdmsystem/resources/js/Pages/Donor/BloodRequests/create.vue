<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    blood_group: '',
    patient_name: '',
    hospital: '',
    city: '',
    reason: ''
});

const submit = () => {
    form.post(route('donor.blood_requests.store'));
};
</script>

<template>
    <Head title="Create Blood Request" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create Blood Request
                </h2>
                <Link
                    :href="route('donor.blood_requests.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Back to Requests
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-2xl sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="blood_group" value="Blood Group Needed" />
                                <select
                                    id="blood_group"
                                    v-model="form.blood_group"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    required
                                >
                                    <option value="">Select Blood Group</option>
                                    <option value="A+">A+</option>
                                    <option value="A-">A-</option>
                                    <option value="B+">B+</option>
                                    <option value="B-">B-</option>
                                    <option value="AB+">AB+</option>
                                    <option value="AB-">AB-</option>
                                    <option value="O+">O+</option>
                                    <option value="O-">O-</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.blood_group" />
                            </div>

                            <div>
                                <InputLabel for="patient_name" value="Patient Name" />
                                <TextInput
                                    id="patient_name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.patient_name"
                                    required
                                    placeholder="Enter patient's full name"
                                />
                                <InputError class="mt-2" :message="form.errors.patient_name" />
                            </div>

                            <div>
                                <InputLabel for="hospital" value="Hospital Name" />
                                <TextInput
                                    id="hospital"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.hospital"
                                    required
                                    placeholder="Enter hospital name"
                                />
                                <InputError class="mt-2" :message="form.errors.hospital" />
                            </div>

                            <div>
                                <InputLabel for="city" value="City" />
                                <TextInput
                                    id="city"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="form.city"
                                    required
                                    placeholder="Enter city name"
                                />
                                <InputError class="mt-2" :message="form.errors.city" />
                            </div>

                            <div>
                                <InputLabel for="reason" value="Reason for Request (Optional)" />
                                <textarea
                                    id="reason"
                                    v-model="form.reason"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    rows="4"
                                    placeholder="Please provide reason for blood request (e.g., surgery, emergency, etc.)"
                                ></textarea>
                                <InputError class="mt-2" :message="form.errors.reason" />
                            </div>

                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm text-blue-700">
                                            <strong>Important:</strong> Your blood request will be reviewed by our admin team. 
                                            You will be notified once it's approved and matching donors are found.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <Link
                                    :href="route('donor.blood_requests.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                    class="bg-red-600 hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:ring-red-500"
                                >
                                    {{ form.processing ? 'Submitting...' : 'Submit Request' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
