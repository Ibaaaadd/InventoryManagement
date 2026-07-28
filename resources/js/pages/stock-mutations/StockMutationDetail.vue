<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuth } from '@/composables/useAuth';
import { useDateFormat } from '@/composables/useDateFormat';
import { useToast } from '@/composables/useToast';
import axios from '@/lib/axios';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseBadge from '@/components/ui/BaseBadge.vue';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseModal from '@/components/ui/BaseModal.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import AuditTrailPanel from '@/components/ui/AuditTrailPanel.vue';
import { FileText, Download, Eye } from 'lucide-vue-next';

const route = useRoute();
const router = useRouter();
const { user } = useAuth();
const { formatDateTime } = useDateFormat();
const { toastSuccess, toastError } = useToast();

const mutation = ref(null);
const loading = ref(false);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const approvalNotes = ref('');
const rejectNotes = ref('');
const submitting = ref(false);

const canApprove = computed(() => {
  return mutation.value?.status === 'pending' && 
         mutation.value?.user?.approver_id === user.value?.id;
});

const pdfViewUrl = computed(() => {
  if (!mutation.value?.id) return null;
  return `/api/stock-mutations/${mutation.value.id}/attachment/view`;
});

onMounted(async () => {
  await fetchMutation();
});

const fetchMutation = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`/stock-mutations/${route.params.id}`);
    mutation.value = response.data;
  } catch (error) {
    console.error('Failed to fetch mutation:', error);
    toastError('Failed to load mutation details');
    router.push('/stock-mutations');
  } finally {
    loading.value = false;
  }
};

const handleApprove = async () => {
  submitting.value = true;
  try {
    await axios.post(`/stock-mutations/${mutation.value.id}/approve`, {
      approval_notes: approvalNotes.value || null
    });
    toastSuccess('Stock mutation approved successfully');
    showApproveModal.value = false;
    await fetchMutation();
  } catch (error) {
    console.error('Failed to approve mutation:', error);
    toastError(error.response?.data?.message || 'Failed to approve mutation');
  } finally {
    submitting.value = false;
  }
};

const handleReject = async () => {
  if (!rejectNotes.value) return;
  submitting.value = true;
  try {
    await axios.post(`/stock-mutations/${mutation.value.id}/reject`, {
      approval_notes: rejectNotes.value
    });
    toastSuccess('Stock mutation rejected');
    showRejectModal.value = false;
    await fetchMutation();
  } catch (error) {
    console.error('Failed to reject mutation:', error);
    toastError(error.response?.data?.message || 'Failed to reject mutation');
  } finally {
    submitting.value = false;
  }
};

const downloadDocument = () => {
  window.open(`/api/stock-mutations/${mutation.value.id}/attachment/download`, '_blank');
};

const viewDocument = () => {
  window.open(`/api/stock-mutations/${mutation.value.id}/attachment/view`, '_blank');
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
  return type === 'in' ? 'success' : 'warning';
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
            <label class="block text-sm font-medium text-gray-700">Item</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.item_name_snapshot }}</p>
            <p class="text-xs text-gray-500">SKU: {{ mutation.item_sku_snapshot }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <div class="mt-1">
              <BaseBadge :variant="getTypeVariant(mutation.type)">
                {{ mutation.type === 'in' ? 'Stock In' : 'Stock Out' }}
              </BaseBadge>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Quantity</label>
            <p class="mt-1 text-sm text-gray-900 font-semibold" :class="{ 'text-green-600': mutation.type === 'in', 'text-red-600': mutation.type === 'out' }">
              {{ mutation.type === 'in' ? '+' : '-' }}{{ mutation.quantity }}
            </p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Transaction Date</label>
            <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(mutation.transaction_date) }}</p>
          </div>
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.notes || 'No notes provided' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Created By</label>
            <p class="mt-1 text-sm text-gray-900">{{ mutation.user?.name || 'Unknown' }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Created At</label>
            <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(mutation.created_at) }}</p>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="mutation.attachment_path" title="Supporting Document" :padding="true" :shadow="true">
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <FileText :size="24" class="text-red-500" />
            <div class="flex-1">
              <p class="text-sm font-medium text-gray-900">Attachment PDF</p>
              <p class="text-xs text-gray-500">Supporting documentation for this mutation</p>
            </div>
            <div class="flex gap-2">
              <BaseButton @click="viewDocument" variant="ghost" size="sm">
                <Eye :size="16" class="mr-1" />
                View
              </BaseButton>
              <BaseButton @click="downloadDocument" variant="ghost" size="sm">
                <Download :size="16" class="mr-1" />
                Download
              </BaseButton>
            </div>
          </div>
          <div class="border rounded-lg overflow-hidden" style="height: 600px;">
            <iframe 
              v-if="pdfViewUrl"
              :src="pdfViewUrl" 
              class="w-full h-full"
              frameborder="0"
            />
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="mutation.approvals && mutation.approvals.length > 0" title="Approval History" :padding="true" :shadow="true">
        <div class="space-y-4">
          <div v-for="approval in mutation.approvals" :key="approval.id" class="border-l-4 pl-4 py-2" :class="{
            'border-green-500': approval.decision === 'approved',
            'border-red-500': approval.decision === 'rejected'
          }">
            <div class="flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-gray-900">{{ approval.approver?.name || 'Unknown' }}</p>
                <p class="text-xs text-gray-500">{{ formatDateTime(approval.approved_at) }}</p>
                <BaseBadge :variant="approval.decision === 'approved' ? 'success' : 'danger'" class="mt-1">
                  {{ approval.decision }}
                </BaseBadge>
                <p v-if="approval.approval_notes" class="text-sm text-gray-700 mt-2">{{ approval.approval_notes }}</p>
              </div>
            </div>
          </div>
        </div>
      </BaseCard>

      <BaseCard v-if="canApprove" title="Approver Actions" :padding="true" :shadow="true">
        <div class="flex gap-3">
          <BaseButton @click="showApproveModal = true" variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Approve Mutation
          </BaseButton>
          <BaseButton @click="showRejectModal = true" variant="danger">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Reject Mutation
          </BaseButton>
        </div>
      </BaseCard>

      <BaseCard :padding="true" :shadow="true">
        <AuditTrailPanel auditable-type="App\Models\StockMutation" :auditable-id="mutation.id" />
      </BaseCard>
    </template>

    <BaseModal v-model="showApproveModal" title="Approve Stock Mutation">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Are you sure you want to approve this stock mutation?
        </p>
        <div v-if="mutation" class="bg-slate-50 p-3 rounded-lg text-sm">
          <p><strong>Item:</strong> {{ mutation.item_name_snapshot }}</p>
          <p><strong>Type:</strong> {{ mutation.type === 'in' ? 'Stock In' : 'Stock Out' }}</p>
          <p><strong>Quantity:</strong> {{ mutation.quantity }}</p>
        </div>
        <BaseTextarea
          v-model="approvalNotes"
          label="Approval Notes (Optional)"
          placeholder="Add any notes for this approval..."
          :rows="3"
        />
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <BaseButton
            variant="ghost"
            @click="showApproveModal = false"
            :disabled="submitting"
          >
            Cancel
          </BaseButton>
          <BaseButton
            @click="handleApprove"
            :loading="submitting"
            :disabled="submitting"
          >
            Approve
          </BaseButton>
        </div>
      </template>
    </BaseModal>

    <BaseModal v-model="showRejectModal" title="Reject Stock Mutation">
      <div class="space-y-4">
        <p class="text-sm text-gray-600">
          Please provide a reason for rejecting this stock mutation.
        </p>
        <div v-if="mutation" class="bg-slate-50 p-3 rounded-lg text-sm">
          <p><strong>Item:</strong> {{ mutation.item_name_snapshot }}</p>
          <p><strong>Type:</strong> {{ mutation.type === 'in' ? 'Stock In' : 'Stock Out' }}</p>
          <p><strong>Quantity:</strong> {{ mutation.quantity }}</p>
        </div>
        <BaseTextarea
          v-model="rejectNotes"
          label="Rejection Reason (Required)"
          placeholder="Explain why this mutation is being rejected..."
          :rows="4"
          required
        />
      </div>
      <template #footer>
        <div class="flex justify-end gap-3">
          <BaseButton
            variant="ghost"
            @click="showRejectModal = false"
            :disabled="submitting"
          >
            Cancel
          </BaseButton>
          <BaseButton
            variant="danger"
            @click="handleReject"
            :loading="submitting"
            :disabled="submitting || !rejectNotes"
          >
            Reject
          </BaseButton>
        </div>
      </template>
    </BaseModal>
  </div>
</template>
