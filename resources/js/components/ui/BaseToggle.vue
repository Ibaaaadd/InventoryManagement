<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  showStatus: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:modelValue']);

const handleToggle = () => {
  if (!props.disabled) {
    emit('update:modelValue', !props.modelValue);
  }
};

const statusText = computed(() => {
  return props.modelValue ? 'Active' : 'Inactive';
});
</script>

<template>
  <div class="flex items-center gap-3">
    <button
      type="button"
      role="switch"
      :aria-checked="modelValue"
      :disabled="disabled"
      @click="handleToggle"
      class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
      :class="[
        modelValue ? 'bg-primary-600' : 'bg-slate-300',
      ]"
    >
      <span
        aria-hidden="true"
        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition-all duration-200 ease-in-out"
        :class="[
          modelValue ? 'translate-x-5' : 'translate-x-0',
        ]"
      />
    </button>
    
    <div v-if="label || showStatus" class="flex flex-col">
      <label v-if="label" class="text-sm font-medium text-slate-700 cursor-pointer" @click="handleToggle">
        {{ label }}
      </label>
      <span v-if="showStatus" class="text-xs text-slate-500">
        {{ statusText }}
      </span>
    </div>
  </div>
</template>
