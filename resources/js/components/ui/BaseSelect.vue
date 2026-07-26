<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: [String, Number, Array],
  options: {
    type: Array,
    required: true,
  },
  label: String,
  placeholder: String,
  error: String,
  required: Boolean,
  disabled: Boolean,
  multiple: Boolean,
  hint: String,
  valueKey: {
    type: String,
    default: 'value',
  },
  labelKey: {
    type: String,
    default: 'label',
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
    <select
      v-model="value"
      :multiple="multiple"
      :disabled="disabled"
      :required="required"
      class="w-full px-3 py-2.5 text-sm border rounded-lg transition-all duration-200 focus:ring-2 focus:ring-offset-0 disabled:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-500"
      :class="{
        'border-danger-300 focus:ring-danger-500 focus:border-danger-500 bg-danger-50/50': error,
        'border-slate-300 focus:ring-primary-500 focus:border-primary-500': !error,
      }"
    >
      <option v-if="placeholder && !multiple" value="">{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option[valueKey]"
        :value="option[valueKey]"
      >
        {{ option[labelKey] }}
      </option>
    </select>
    <p v-if="hint && !error" class="text-xs text-slate-500">{{ hint }}</p>
    <p v-if="error" class="text-xs text-danger-600 flex items-center gap-1">
      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
      </svg>
      {{ error }}
    </p>
  </div>
</template>
