<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  donor: Object,
})

const form = useForm({
  contact_number: props.donor.contact_number || '',
  address: props.donor.address || '',
  city: props.donor.city || '',
  state: props.donor.state || '',
  zip_code: props.donor.zip_code || '',
  is_available: props.donor.is_available || false,
})

const submit = () => {
  form.patch(route('donor.profile.update'))
}
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
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
          <form @submit.prevent="submit" class="space-y-6">
            <div>
              <label class="block font-semibold text-gray-700">Contact Number</label>
              <input v-model="form.contact_number" type="text" class="w-full border border-gray-300 rounded p-2" />
            </div>

            <div>
              <label class="block font-semibold text-gray-700">Address</label>
              <input v-model="form.address" type="text" class="w-full border border-gray-300 rounded p-2" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block font-semibold text-gray-700">City</label>
                <input v-model="form.city" type="text" class="w-full border border-gray-300 rounded p-2" />
              </div>
              <div>
                <label class="block font-semibold text-gray-700">State</label>
                <input v-model="form.state" type="text" class="w-full border border-gray-300 rounded p-2" />
              </div>
            </div>

            <div>
              <label class="block font-semibold text-gray-700">Zip Code</label>
              <input v-model="form.zip_code" type="text" class="w-full border border-gray-300 rounded p-2" />
            </div>

            <div class="flex items-center gap-2">
              <input id="available" type="checkbox" v-model="form.is_available" />
              <label for="available" class="text-sm text-gray-700">Available to Donate?</label>
            </div>

            <div>
              <button :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Update Profile
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
