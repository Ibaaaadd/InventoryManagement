<script setup>
import { computed } from 'vue';
import { XCircle } from 'lucide-vue-next';

const props = defineProps({
  modelValue: String,
  label: String,
  placeholder: String,
  error: String,
  required: Boolean,
  disabled: Boolean,
  hint: String,
  rows: {
    type: Number,
    default: 4,
  },
});

const emit = defineEmits(['update:modelValue']);

const value = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});
</script>

<template>
  <div class="space-y-1.5">
    <label v-if="label" class="block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="required" class="text-danger-600">*</span>
    </label>
    <textarea
      v-model="value"
      :placeholder="placeholder"
      :disabled="disabled"
      :required="required"
      :rows="rows"
      class="w-full px-3 py-2.5 text-sm border rounded-lg focus:outline-none transition-colors duration-150 focus:ring-2 focus:ring-offset-0 disabled:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-500 resize-none"
      :class="{
        'border-danger-300 focus:ring-danger-500/20 focus:border-danger-500 bg-danger-50/50': error,
        'border-slate-300 focus:ring-primary-500/20 focus:border-primary-500': !error,
      }"
    ></textarea>
    <p v-if="hint && !error" class="text-xs text-slate-500">{{ hint }}</p>
    <p v-if="error" class="text-xs text-danger-600 flex items-center gap-1">
      <XCircle :size="16" />
      {{ error }}
    </p>
  </div>
</template>
