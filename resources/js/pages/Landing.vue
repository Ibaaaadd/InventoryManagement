<script setup>
import { useRouter } from 'vue-router';
import GuestLayout from '@/components/layout/GuestLayout.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseButton from '@/components/ui/BaseButton.vue';

const router = useRouter();

const navigateToLogin = () => {
  router.push('/login');
};
</script>

<template>
  <GuestLayout>
    <section class="relative py-20 overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-gray-50"></div>
      <div class="relative container mx-auto px-4">
        <div class="max-w-4xl mx-auto text-center">
          <h1 class="text-5xl md:text-6xl font-bold text-gray-900 leading-tight">
            Inventory Management System
          </h1>
          <p class="mt-6 text-xl text-gray-600 leading-relaxed">
            Streamline your inventory workflow with seamless collaboration between warehouse staff and management. 
            Track every movement, approve every change, and maintain complete transparency with automated audit trails.
          </p>
          <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <BaseButton @click="navigateToLogin" variant="primary" size="lg">
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
              </svg>
              Sign In to System
            </BaseButton>
          </div>
        </div>
      </div>
    </section>

    <section class="py-16 bg-white">
      <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900">How It Works</h2>
          <p class="mt-4 text-lg text-gray-600">Three simple steps to manage your inventory with complete accountability</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <BaseCard :padding="true" :shadow="true" class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 mb-4">
              <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Staff Records Mutations</h3>
            <p class="text-gray-600">
              Warehouse staff logs incoming and outgoing inventory with supporting documentation. 
              Every entry includes item details, quantities, and PDF attachments for verification.
            </p>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true" class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
              <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Manager Reviews & Approves</h3>
            <p class="text-gray-600">
              Managers review submitted mutations, verify documentation, and approve or reject changes. 
              Every decision is recorded with timestamps and optional rejection reasons.
            </p>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true" class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-purple-100 mb-4">
              <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Audit Trail Preserved</h3>
            <p class="text-gray-600">
              Complete history of changes is automatically recorded. Track who did what, when, and why. 
              Immutable records ensure compliance and accountability across all operations.
            </p>
          </BaseCard>
        </div>
      </div>
    </section>

    <section class="py-16 bg-gray-50">
      <div class="container mx-auto px-4">
        <div class="max-w-3xl mx-auto text-center mb-12">
          <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Powerful Features</h2>
          <p class="mt-4 text-lg text-gray-600">Everything you need to manage inventory with confidence and clarity</p>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <BaseCard :padding="true" :shadow="true">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-100">
                  <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Master Item Management</h3>
                <p class="text-gray-600">
                  Organize inventory with detailed item records including codes, categories, units, and storage locations. 
                  Track stock levels with visual indicators for low inventory.
                </p>
              </div>
            </div>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-green-100">
                  <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Two-Level Approval Workflow</h3>
                <p class="text-gray-600">
                  Staff proposes changes, managers approve. Clear separation of duties ensures accuracy and prevents unauthorized modifications to inventory records.
                </p>
              </div>
            </div>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-purple-100">
                  <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Automatic Audit Trails</h3>
                <p class="text-gray-600">
                  Every action is logged automatically with user, timestamp, and change details. 
                  Complete transparency and accountability without manual record-keeping.
                </p>
              </div>
            </div>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-yellow-100">
                  <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Document Management</h3>
                <p class="text-gray-600">
                  Attach PDF documents to every mutation with built-in validation. 
                  Store delivery notes, invoices, and authorization forms securely with size and format controls.
                </p>
              </div>
            </div>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-red-100">
                  <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Export & Import with Background Jobs</h3>
                <p class="text-gray-600">
                  Handle large Excel exports and imports efficiently with background job processing. 
                  Queue heavy operations to avoid blocking, with progress tracking and notifications.
                </p>
              </div>
            </div>
          </BaseCard>

          <BaseCard :padding="true" :shadow="true">
            <div class="flex items-start gap-4">
              <div class="flex-shrink-0">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-indigo-100">
                  <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  </svg>
                </div>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Immutable Transaction History</h3>
                <p class="text-gray-600">
                  Once approved, transactions cannot be modified or deleted. 
                  Maintain data integrity with immutable snapshots that preserve historical accuracy for auditing.
                </p>
              </div>
            </div>
          </BaseCard>
        </div>
      </div>
    </section>

    <footer class="py-12 bg-gray-900 text-white">
      <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto text-center">
          <h3 class="text-2xl font-bold mb-4">Ready to streamline your inventory?</h3>
          <p class="text-gray-400 mb-6">Contact your system administrator for access credentials</p>
          <BaseButton @click="navigateToLogin" variant="primary" size="lg">
            Sign In Now
          </BaseButton>
          <div class="mt-12 pt-8 border-t border-gray-800">
            <p class="text-gray-400 text-sm">
              &copy; {{ new Date().getFullYear() }} Inventory Management System. All rights reserved.
            </p>
          </div>
        </div>
      </div>
    </footer>
  </GuestLayout>
</template>
