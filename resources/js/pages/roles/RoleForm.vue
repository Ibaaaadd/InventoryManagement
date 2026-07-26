<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from '@/lib/axios';
import { showError } from '@/lib/swal';
import { useToast } from '@/composables/useToast';
import { Save, X, ArrowLeft } from 'lucide-vue-next';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';
import AuditTrailPanel from '@/components/ui/AuditTrailPanel.vue';

const router = useRouter();
const route = useRoute();
const { toastSuccess } = useToast();

const isEditMode = computed(() => !!route.params.id);
const pageTitle = computed(() => isEditMode.value ? 'Edit Role' : 'Create New Role');

const form = ref({
  name: '',
  description: '',
});

const errors = ref({
  name: '',
  description: '',
});

const loading = ref(false);
const submitting = ref(false);
const roleName = ref('');

onMounted(async () => {
  if (isEditMode.value) {
    try {
      loading.value = true;
      const response = await axios.get(`/roles/${route.params.id}`);
      const roleData = response.data;
      
      roleName.value = roleData.name;
      
      form.value.name = roleData.name;
      form.value.description = roleData.description;
  } catch (error) {
    console.error('Failed to fetch role:', error);
    if (error.response?.status === 404) {
      showError('Role not found');
    } else {
      showError('Failed to load role data');
    }
    router.push({ name: 'RoleList' });
    } finally {
      loading.value = false;
    }
  }
});

const validateForm = () => {
  let isValid = true;
  errors.value = { name: '', description: '' };

  if (!form.value.name.trim()) {
    errors.value.name = 'Role name is required';
    isValid = false;
  } else if (form.value.name.trim().length < 3) {
    errors.value.name = 'Role name must be at least 3 characters';
    isValid = false;
  }

  if (!form.value.description.trim()) {
    errors.value.description = 'Description is required';
    isValid = false;
  }

  return isValid;
};

const handleSubmit = async () => {
  if (!validateForm()) return;

  try {
    submitting.value = true;
    
    if (isEditMode.value) {
      await axios.put(`/roles/${route.params.id}`, form.value);
      toastSuccess('Role Updated', 'Role information has been updated successfully');
    } else {
      await axios.post('/roles', form.value);
      toastSuccess('Role Created', 'New role has been added successfully');
    }
    
    router.push({ name: 'RoleList' });
  } catch (error) {
    console.error('Failed to submit role:', error);
    
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      showError(error.response?.data?.message || 'Failed to save role. Please try again.');
    }
  } finally {
    submitting.value = false;
  }
};

const handleCancel = () => {
  router.push({ name: 'RoleList' });
};
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center gap-4">
      <button
        @click="handleCancel"
        class="p-2 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors"
      >
        <ArrowLeft :size="20" />
      </button>
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">{{ pageTitle }}</h1>
        <p class="text-sm text-slate-600 mt-1">
          {{ 'Create a new role for system users' }}
        </p>
      </div>
    </div>

    <BaseCard>
      <div v-if="loading" class="space-y-4">
        <div class="animate-pulse space-y-4">
          <div class="h-10 bg-slate-200 rounded"></div>
          <div class="h-24 bg-slate-200 rounded"></div>
        </div>
      </div>

      <form v-else @submit.prevent="handleSubmit" class="space-y-6">
        <BaseInput
          v-model="form.name"
          label="Role Name"
          type="text"
          placeholder="Enter role name (e.g., Administrator, Manager)"
          :error="errors.name"
          :required="true"
          :disabled="submitting"
          hint="Minimum 3 characters"
        />

        <BaseTextarea
          v-model="form.description"
          label="Description"
          :rows="4"
          placeholder="Describe the role's responsibilities and permissions"
          :error="errors.description"
          :required="true"
          :disabled="submitting"
        />

        <div class="flex items-center gap-3 pt-4 border-t border-slate-200">
          <BaseButton
            type="submit"
            :loading="submitting"
            :disabled="submitting"
          >
            <Save :size="18" />
            {{ isEditMode ? 'Update Role' : 'Create Role' }}
          </BaseButton>
          <BaseButton
            type="button"
            variant="ghost"
            @click="handleCancel"
            :disabled="submitting"
          >
            <X :size="18" />
            Cancel
          </BaseButton>
        </div>
      </form>
    </BaseCard>

    <BaseCard v-if="isEditMode" :padding="true">
      <AuditTrailPanel
        auditable-type="App\Models\Role"
        :auditable-id="route.params.id"
      />
    </BaseCard>
  </div>
</template>
