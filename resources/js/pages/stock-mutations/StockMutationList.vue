<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import { Plus, Eye, CheckCircle, XCircle, Download, Upload, RefreshCw, Search, Filter, Pencil, Trash2 } from "lucide-vue-next";
import { useStatusBadge } from "@/composables/useStatusBadge";
import { useDateFormat } from "@/composables/useDateFormat";
import { useAuth } from "@/composables/useAuth";
import { useToast } from "@/composables/useToast";
import axios from "@/lib/axios";
import BaseSearchableSelect from "@/components/ui/BaseSearchableSelect.vue";
import BaseCard from "@/components/ui/BaseCard.vue";
import DataTable from "@/components/ui/DataTable.vue";
import BaseButton from "@/components/ui/BaseButton.vue";
import BaseBadge from "@/components/ui/BaseBadge.vue";
import BaseModal from "@/components/ui/BaseModal.vue";
import BaseTextarea from "@/components/ui/BaseTextarea.vue";
import BaseSelect from "@/components/ui/BaseSelect.vue";
import SearchFilterBar from "@/components/ui/SearchFilterBar.vue";
import ExportModal from "@/components/ui/ExportModal.vue";
import ImportModal from "@/components/ui/ImportModal.vue";

const router = useRouter();
const { getVariant } = useStatusBadge();
const { formatDate } = useDateFormat();
const { user } = useAuth();
const { toastSuccess, toastError } = useToast();

const mutations = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const typeFilter = ref("");
const statusFilter = ref("");
const currentPage = ref(1);
const perPage = ref(10);
const total = ref(0);

const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showDeleteModal = ref(false);
const showExportModal = ref(false);
const showImportModal = ref(false);
const selectedMutation = ref(null);
const approvalNotes = ref("");
const rejectNotes = ref("");
const processingApproval = ref(false);

const lastPage = computed(() => Math.ceil(total.value / perPage.value) || 1);

const typeOptions = [
    { value: '', label: 'All Types' },
    { value: 'in', label: 'Stock In' },
    { value: 'out', label: 'Stock Out' }
];

const statusOptions = [
    { value: '', label: 'All Status' },
    { value: 'pending', label: 'Pending' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' }
];

const columns = [
    { key: "item_name_snapshot", label: "Item", sortable: true },
    { key: "type", label: "Type", sortable: false },
    { key: "quantity", label: "Quantity", sortable: true },
    { key: "status", label: "Status", sortable: false },
    { key: "user", label: "Created By", sortable: false },
    { key: "transaction_date", label: "Date", sortable: true },
];

onMounted(() => {
    fetchMutations();
});

const fetchMutations = async () => {
    loading.value = true;
    try {
        const params = new URLSearchParams();
        if (searchQuery.value) params.append('search', searchQuery.value);
        if (typeFilter.value) params.append('type', typeFilter.value);
        if (statusFilter.value) params.append('status', statusFilter.value);
        if (currentPage.value) params.append('page', currentPage.value);
        
        const response = await axios.get(`/stock-mutations?${params.toString()}`);
        mutations.value = response.data.data || [];
        total.value = response.data.total || 0;
        currentPage.value = response.data.current_page || 1;
    } catch (error) {
        console.error("Failed to fetch mutations:", error);
        toastError("Failed to load mutations");
    } finally {
        loading.value = false;
    }
};

const handleSearch = () => {
    currentPage.value = 1;
    fetchMutations();
};

const handleSearchInput = () => {
    if (searchQuery.value.length >= 2 || searchQuery.value.length === 0) {
        currentPage.value = 1;
        fetchMutations();
    }
};

const handleClear = () => {
    searchQuery.value = "";
    typeFilter.value = "";
    statusFilter.value = "";
    currentPage.value = 1;
    fetchMutations();
};

const handleTypeFilterChange = () => {
    currentPage.value = 1;
    fetchMutations();
};

const handleStatusFilterChange = () => {
    currentPage.value = 1;
    fetchMutations();
};

const clearFilters = () => {
    typeFilter.value = "";
    statusFilter.value = "";
    currentPage.value = 1;
    fetchMutations();
};

const handlePageChange = (page) => {
    currentPage.value = page;
    fetchMutations();
};

const handleRowClick = (row) => {
    router.push(`/stock-mutations/${row.id}`);
};

const navigateToCreate = () => {
    router.push("/stock-mutations/create");
};

const openExportModal = () => {
    showExportModal.value = true;
};

const openImportModal = () => {
    if (!user.value?.approver_id) {
        toastError('You do not have an assigned approver. Please contact administrator to set up your approver.');
        return;
    }
    showImportModal.value = true;
};

const handleExported = (jobId) => {
    fetchMutations();
    router.push({ name: "ExportImportHistory" });
};

const handleImported = (jobId) => {
    fetchMutations();
    router.push({ name: "ExportImportHistory" });
};

const handleRefresh = () => {
    fetchMutations();
};

const handleView = (row) => {
    router.push(`/stock-mutations/${row.id}`);
};

const getTypeVariant = (type) => {
    return type === "in" ? "success" : "warning";
};

const canApprove = (mutation) => {
    return mutation.status === 'pending' && mutation.user?.approver_id === user.value?.id;
};

const canEdit = (mutation) => {
    return mutation.status === 'pending' && mutation.user_id === user.value?.id;
};

const canDelete = (mutation) => {
    return mutation.status === 'pending' && mutation.user_id === user.value?.id;
};

const openApproveModal = (mutation) => {
    selectedMutation.value = mutation;
    approvalNotes.value = "";
    showApproveModal.value = true;
};

const openRejectModal = (mutation) => {
    selectedMutation.value = mutation;
    rejectNotes.value = "";
    showRejectModal.value = true;
};

const openDeleteModal = (mutation) => {
    selectedMutation.value = mutation;
    showDeleteModal.value = true;
};

const handleApprove = async () => {
    if (!selectedMutation.value) return;
    processingApproval.value = true;
    try {
        await axios.post(`/stock-mutations/${selectedMutation.value.id}/approve`, {
            approval_notes: approvalNotes.value || null
        });
        toastSuccess("Stock mutation approved successfully");
        showApproveModal.value = false;
        await fetchMutations();
    } catch (error) {
        console.error("Failed to approve mutation:", error);
        toastError(error.response?.data?.message || "Failed to approve mutation");
    } finally {
        processingApproval.value = false;
    }
};

const handleReject = async () => {
    if (!selectedMutation.value || !rejectNotes.value) return;
    processingApproval.value = true;
    try {
        await axios.post(`/stock-mutations/${selectedMutation.value.id}/reject`, {
            approval_notes: rejectNotes.value
        });
        toastSuccess("Stock mutation rejected");
        showRejectModal.value = false;
        await fetchMutations();
    } catch (error) {
        console.error("Failed to reject mutation:", error);
        toastError(error.response?.data?.message || "Failed to reject mutation");
    } finally {
        processingApproval.value = false;
    }
};

const handleDelete = async () => {
    if (!selectedMutation.value) return;
    loading.value = true;
    try {
        await axios.delete(`/stock-mutations/${selectedMutation.value.id}`);
        toastSuccess("Stock mutation deleted successfully");
        showDeleteModal.value = false;
        await fetchMutations();
    } catch (error) {
        console.error("Failed to delete mutation:", error);
        toastError(error.response?.data?.message || "Failed to delete mutation");
    } finally {
        loading.value = false;
    }
};
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Stock Mutations
                </h1>
                <p class="mt-1 text-sm text-slate-600">
                    Track all inventory movements and changes
                </p>
            </div>
            <div class="flex items-center gap-2">
                <BaseButton @click="openExportModal" variant="secondary">
                    <Download :size="18" />
                    Export
                </BaseButton>
                <BaseButton @click="openImportModal" variant="secondary">
                    <Upload :size="18" />
                    Import
                </BaseButton>
                <BaseButton @click="navigateToCreate">
                    <Plus :size="18" />
                    New Mutation
                </BaseButton>
            </div>
        </div>

        <BaseCard>
            <div class="flex items-center gap-3">
                <div class="relative flex-1">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" 
                        :size="20"
                    />
                    <input
                        v-model="searchQuery"
                        @input="handleSearchInput"
                        type="text"
                        placeholder="Search mutations by item name or notes..."
                        class="w-full pl-10 pr-4 py-2.5 text-sm border focus:outline-none border-slate-300 rounded-lg focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 transition-all"
                    />
                </div>
                <div class="flex items-center gap-2 min-w-[280px]">
                    <Filter :size="18" class="text-slate-500 flex-shrink-0" />
                    <BaseSearchableSelect
                        v-model="typeFilter"
                        placeholder="Filter type"
                        :options="typeOptions"
                        @update:model-value="handleTypeFilterChange"
                        class="flex-1"
                    />
                    <BaseSearchableSelect
                        v-model="statusFilter"
                        placeholder="Filter status"
                        :options="statusOptions"
                        @update:model-value="handleStatusFilterChange"
                        class="flex-1"
                    />
                </div>
                <BaseButton
                        v-if="typeFilter || statusFilter"
                        @click="clearFilters"
                        variant="ghost"
                        size="sm"
                    >
                        Clear
                    </BaseButton>
                <button
                    @click="handleRefresh"
                    :disabled="loading"
                    class="p-2.5 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-all disabled:opacity-50 disabled:cursor-not-allowed flex-shrink-0"
                    title="Refresh"
                >
                    <RefreshCw :size="20" :class="{ 'animate-spin': loading }" />
                </button>
            </div>
        </BaseCard>
        <DataTable
            :columns="columns"
            :data="mutations"
            :loading="loading"
            empty-message="No stock mutations found"
            :show-pagination="true"
            :current-page="currentPage"
            :last-page="lastPage"
            :total="total"
            @sort="handleSort"
            @row-click="handleRowClick"
            @page-change="handlePageChange"
        >
            <template #cell-type="{ value }">
                <BaseBadge :variant="getTypeVariant(value)">
                    {{ value === 'in' ? 'IN' : 'OUT' }}
                </BaseBadge>
            </template>

            <template #cell-status="{ value }">
                <BaseBadge :variant="getVariant(value)">
                    {{ value }}
                </BaseBadge>
            </template>

            <template #cell-user="{ row }">
                {{ row.user?.name || 'Unknown' }}
            </template>

            <template #cell-transaction_date="{ value }">
                {{ formatDate(value) }}
            </template>

            <template #cell-quantity="{ value, row }">
                <span
                    :class="{
                        'text-success-600 font-semibold': row.type === 'in',
                        'text-danger-600 font-semibold': row.type === 'out',
                    }"
                >
                    {{ row.type === "in" ? "+" : "-" }}{{ value }}
                </span>
            </template>

            <template #actions="{ row }">
                <div class="flex items-center gap-1">
                    <button
                        @click.stop="handleView(row)"
                        class="p-2 text-slate-600 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition-colors"
                        title="View Details"
                    >
                        <Eye :size="16" />
                    </button>
                    <button
                        v-if="canEdit(row)"
                        @click.stop="router.push(`/stock-mutations/${row.id}/edit`)"
                        class="p-2 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                        title="Edit"
                    >
                        <Pencil :size="16" />
                    </button>
                    <button
                        v-if="canDelete(row)"
                        @click.stop="openDeleteModal(row)"
                        class="p-2 text-slate-600 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors"
                        title="Delete"
                    >
                        <Trash2 :size="16" />
                    </button>
                    <button
                        v-if="canApprove(row)"
                        @click.stop="openApproveModal(row)"
                        class="p-2 text-success-600 hover:text-success-700 hover:bg-success-50 rounded-lg transition-colors"
                        title="Approve"
                    >
                        <CheckCircle :size="16" />
                    </button>
                    <button
                        v-if="canApprove(row)"
                        @click.stop="openRejectModal(row)"
                        class="p-2 text-danger-600 hover:text-danger-700 hover:bg-danger-50 rounded-lg transition-colors"
                        title="Reject"
                    >
                        <XCircle :size="16" />
                    </button>
                </div>
            </template>
        </DataTable>

        <BaseModal v-model="showApproveModal" title="Approve Stock Mutation">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Are you sure you want to approve this stock mutation?
                </p>
                <div v-if="selectedMutation" class="bg-slate-50 p-3 rounded-lg text-sm">
                    <p><strong>Item:</strong> {{ selectedMutation.item_name_snapshot }}</p>
                    <p><strong>Type:</strong> {{ selectedMutation.type === 'in' ? 'Stock In' : 'Stock Out' }}</p>
                    <p><strong>Quantity:</strong> {{ selectedMutation.quantity }}</p>
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
                        :disabled="processingApproval"
                    >
                        Cancel
                    </BaseButton>
                    <BaseButton
                        @click="handleApprove"
                        :loading="processingApproval"
                        :disabled="processingApproval"
                    >
                        <CheckCircle :size="18" />
                        Approve
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <BaseModal v-model="showRejectModal" title="Reject Stock Mutation">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Please provide a reason for rejecting this stock mutation.
                </p>
                <div v-if="selectedMutation" class="bg-slate-50 p-3 rounded-lg text-sm">
                    <p><strong>Item:</strong> {{ selectedMutation.item_name_snapshot }}</p>
                    <p><strong>Type:</strong> {{ selectedMutation.type === 'in' ? 'Stock In' : 'Stock Out' }}</p>
                    <p><strong>Quantity:</strong> {{ selectedMutation.quantity }}</p>
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
                        :disabled="processingApproval"
                    >
                        Cancel
                    </BaseButton>
                    <BaseButton
                        variant="danger"
                        @click="handleReject"
                        :loading="processingApproval"
                        :disabled="processingApproval || !rejectNotes"
                    >
                        <XCircle :size="18" />
                        Reject
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <BaseModal v-model="showDeleteModal" title="Delete Stock Mutation">
            <div class="space-y-4">
                <p class="text-sm text-slate-600">
                    Are you sure you want to delete this stock mutation? This action cannot be undone.
                </p>
                <div v-if="selectedMutation" class="bg-slate-50 p-3 rounded-lg text-sm">
                    <p><strong>Item:</strong> {{ selectedMutation.item_name_snapshot }}</p>
                    <p><strong>Type:</strong> {{ selectedMutation.type === 'in' ? 'Stock In' : 'Stock Out' }}</p>
                    <p><strong>Quantity:</strong> {{ selectedMutation.quantity }}</p>
                </div>
            </div>
            <template #footer>
                <div class="flex justify-end gap-3">
                    <BaseButton
                        variant="ghost"
                        @click="showDeleteModal = false"
                        :disabled="loading"
                    >
                        Cancel
                    </BaseButton>
                    <BaseButton
                        variant="danger"
                        @click="handleDelete"
                        :loading="loading"
                        :disabled="loading"
                    >
                        <Trash2 :size="18" />
                        Delete
                    </BaseButton>
                </div>
            </template>
        </BaseModal>

        <ExportModal
            v-model="showExportModal"
            model="stock-mutation"
            @exported="handleExported"
        />

        <ImportModal
            v-model="showImportModal"
            model="stock-mutation"
            @imported="handleImported"
        />
    </div>
</template>
