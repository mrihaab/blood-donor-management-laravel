<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
  donations: Array
});
</script>

<template>
  <Head title="Donation History" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold text-gray-800">Donation History</h2>
    </template>

    <div class="py-10 max-w-6xl mx-auto px-4">
      <div class="bg-white rounded shadow p-6">
        <table class="min-w-full border">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-2 border">#</th>
              <th class="p-2 border">Date</th>
              <th class="p-2 border">Quantity</th>
              <th class="p-2 border">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(donation, index) in donations" :key="donation.id" class="text-center">
              <td class="p-2 border">{{ index + 1 }}</td>
              <td class="p-2 border">{{ new Date(donation.created_at).toLocaleDateString() }}</td>
              <td class="p-2 border">{{ donation.quantity }} ml</td>
              <td class="p-2 border">
                <span :class="donation.status === 'available' ? 'text-green-600' : 'text-gray-600'">
                  {{ donation.status }}
                </span>
              </td>
            </tr>
            <tr v-if="donations.length === 0">
              <td colspan="4" class="p-4 text-center text-gray-500">No donation records found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
