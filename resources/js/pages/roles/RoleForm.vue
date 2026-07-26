<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { Save, X, ArrowLeft } from 'lucide-vue-next';
import BaseButton from '@/components/ui/BaseButton.vue';
import BaseCard from '@/components/ui/BaseCard.vue';
import BaseInput from '@/components/ui/BaseInput.vue';
import BaseTextarea from '@/components/ui/BaseTextarea.vue';

const router = useRouter();
const route = useRoute();

const isEditMode = computed(() => !!route.params.id);
const pageTitle = computed(() => isEditMode.value ? 'Edit Role' : 'Create New Role');

// TODO: ganti dengan data dari API GET /api/roles/:id untuk edit mode
const dummyRoles = {
  1: { id: 1, name: 'Administrator', description: 'Full system access with all permissions' },
  2: { id: 2, name: 'Manager', description: 'Can manage items, view reports, and approve mutations' },
  3: { id: 3, name: 'Staff', description: 'Can create and view stock mutations' },
};

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

onMounted(() => {
  if (isEditMode.value) {
    loading.value = true;
    
    // TODO: ganti dengan fetch API GET /api/roles/:id
    setTimeout(() => {
      const roleId = parseInt(route.params.id);
      const roleData = dummyRoles[roleId];
      
      if (roleData) {
        form.value.name = roleData.name;
        form.value.description = roleData.description;
      } else {
        alert('Role not found');
        router.push({ name: 'RoleList' });
      }
      
      loading.value = false;
    }, 500);
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

  submitting.value = true;

  // TODO: ganti dengan POST /api/roles atau PUT /api/roles/:id
  console.log('Form submitted:', {
    mode: isEditMode.value ? 'edit' : 'create',
    roleId: route.params.id,
    data: form.value,
  });

  setTimeout(() => {
    submitting.value = false;
    alert(`Role ${isEditMode.value ? 'updated' : 'created'} successfully!`);
    router.push({ name: 'RoleList' });
  }, 1000);
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
          {{ isEditMode ? 'Update role information and permissions' : 'Create a new role for system users' }}
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
  </div>
</template>
