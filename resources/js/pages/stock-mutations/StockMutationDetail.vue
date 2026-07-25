<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import AuditTrailPanel from '@/components/ui/AuditTrailPanel.vue';

const route = useRoute();
const router = useRouter();
const { isManager, isStaff } = useAuth();

const mutation = ref(null);
const loading = ref(false);
const showApprovalModal = ref(false);
const approvalAction = ref('');
const approvalReason = ref('');
const submitting = ref(false);

onMounted(async () => {
  await fetchMutation();
});

const fetchMutation = async () => {
  loading.value = true;
  try {
    mutation.value = {
      id: route.params.id,
      code: 'MUT001',
      item: { code: 'ITM001', name: 'Laptop Dell XPS 15' },
      type: 'IN',
      quantity: 10,
      status: 'pending',
      notes: 'New stock arrival from supplier',
      document_url: '/storage/documents/mut001.pdf',
      created_by: { name: 'John Doe', email: 'john@example.com' },
      created_at: '2026-07-25 10:30:00',
      approved_by: null,
      approved_at: null,
      approval_reason: null,
    };
  } catch (error) {
    console.error('Failed to fetch mutation:', error);
  } finally {
    loading.value = false;
  }
};

const openApprovalModal = (action) => {
  approvalAction.value = action;
  approvalReason.value = '';
  showApprovalModal.value = true;
};

const handleApproval = async () => {
  if (approvalAction.value === 'reject' && !approvalReason.value) {
    alert('Please provide a reason for rejection');
    return;
  }

  submitting.value = true;
  try {
    console.log(`${approvalAction.value} mutation:`, mutation.value.id, approvalReason.value);
    await new Promise(resolve => setTimeout(resolve, 1000));
    showApprovalModal.value = false;
    await fetchMutation();
  } catch (error) {
    console.error('Failed to process approval:', error);
  } finally {
    submitting.value = false;
  }
};

const downloadDocument = () => {
  console.log('Download document:', mutation.value.document_url);
};

const goBack = () => {
  router.push('/stock-mutations');
};

const getStatusVariant = (status) => {
  const variants = {
    pending: 'pending',
    approved: 'approved',
    rejected: 'rejected',
  };
  return variants[status] || 'default';
};

const getTypeVariant = (type) => {
  return type === 'IN' ? 'success' : 'warning';
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3">
          <BaseButton @click="goBack" variant="ghost" size="sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
          </BaseButton>
          <h1 class="text-2xl font-bold text-gray-900">Mutation Details</h1>
        </div>
        <p class="mt-1 text-sm text-gray-600">View and manage stock mutation information</p>
      </div>
      <BaseBadge v-if="mutation" :variant="getStatusVariant(mutation.status)" size="lg">
        {{ mutation.status.toUpperCase() }}
      </BaseBadge>
    </div>

    <div v-if="loading" class="text-center py-12">
      <p class="text-gray-500">Loading mutation details...</p>
    </div>

    <template v-else-if="mutation">
      <BaseCard title="Mutation Information" :padding="true" :shadow="true">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Mutation Code</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.code }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <div class="mt-1">
              <BaseBadge :variant="getTypeVariant(mutation.type)">
                {{ mutation.type }}
              </BaseBadge>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Item</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.item.name }} ({{ mutation.item.code }})</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Quantity</label>
            <p class="mt-1 text-sm text-gray-900 font-semibold" :class="{ 'text-green-600': mutation.type === 'IN', 'text-red-600': mutation.type === 'OUT' }">
              {{ mutation.type === 'IN' ? '+' : '-' }}{{ mutation.quantity }}
            </p>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.notes || 'No notes provided' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Created By</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.created_by.name }}</p>
            <p class="text-xs text-gray-500">{{ mutation.created_by.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Created At</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.created_at }}</p>
          </div>
          <div v-if="mutation.document_url">
            <label class="block text-sm font-medium text-gray-700">Supporting Document</label>
            <BaseButton @click="downloadDocument" variant="ghost" size="sm" class="mt-1">
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Download PDF
            </BaseButton>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="mutation.status !== 'pending'" title="Approval Information" :padding="true" :shadow="true">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <label class="block text-sm font-medium text-gray-700">Approved/Rejected By</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.approved_by || 'N/A' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Date</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.approved_at || 'N/A' }}</p>
          </div>
          <div class="md:col-span-2" v-if="mutation.approval_reason">
            <label class="block text-sm font-medium text-gray-700">Reason</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.approval_reason }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="isManager && mutation.status === 'pending'" title="Manager Actions" :padding="true" :shadow="true">
        <div class="flex gap-3">
          <BaseButton @click="openApprovalModal('approve')" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Approve Mutation
          </BaseButton>
          <BaseButton @click="openApprovalModal('reject')" variant="danger">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Reject Mutation
          </BaseButton>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <AuditTrailPanel auditable-type="StockMutation" :auditable-id="mutation.id" />
      </BaseCard>
    </template>

    <BaseModal v-model="showApprovalModal" :title="`${approvalAction === 'approve' ? 'Approve' : 'Reject'} Mutation`" size="md">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to {{ approvalAction }} this stock mutation?
        </p>
        <BaseInput
          v-if="approvalAction === 'reject'"
          v-model="approvalReason"
          label="Reason for Rejection"
          placeholder="Enter reason..."
          :required="true"
        />
      </div>
      <template #footer>
        <BaseButton @click="showApprovalModal = false" variant="secondary" :disabled="submitting">
          Cancel
        </BaseButton>
        <BaseButton
          @click="handleApproval"
          :variant="approvalAction === 'approve' ? 'primary' : 'danger'"
          :loading="submitting"
          :disabled="submitting"
        >
          {{ approvalAction === 'approve' ? 'Approve' : 'Reject' }}
        </BaseButton>
      </template>
    </BaseModal>
  </div>
</template>
