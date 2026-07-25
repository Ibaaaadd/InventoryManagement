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
  <div class="space-y-1">
    <label v-if="label" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <select
      v-model="value"
      :multiple="multiple"
      :disabled="disabled"
      :required="required"
      class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-gray-100 disabled:cursor-not-allowed"
      :class="{
        'border-red-300 focus:ring-red-500 focus:border-red-500': error,
        'border-gray-300': !error,
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
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>
