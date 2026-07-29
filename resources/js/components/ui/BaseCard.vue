<script setup>
defineProps({
  title: String,
  subtitle: String,
  shadow: {
    type: String,
    default: 'sm',
    validator: (value) => ['none', 'sm', 'md', 'lg'].includes(value),
  },
  padding: {
    type: Boolean,
    default: true,
  },
  hover: {
    type: Boolean,
    default: false,
  },
});

const shadowClasses = {
  none: '',
  sm: 'shadow-sm',
  md: 'shadow-md',
  lg: 'shadow-lg',
};
</script>

<template>
  <div
    class="bg-white rounded-lg border border-slate-200 transition-all duration-200"
    :class="[
      shadowClasses[shadow],
      { 'p-6': padding },
      { 'hover:shadow-md hover:border-slate-300': hover }
    ]"
  >
    <div v-if="title || subtitle || $slots['header-actions']" class="mb-4">
      <div class="flex items-center justify-between">
        <div>
          <h3 v-if="title" class="text-lg font-semibold text-slate-900 tracking-tight">
            {{ title }}
          </h3>
          <p v-if="subtitle" class="text-sm text-slate-600 mt-1">
            {{ subtitle }}
          </p>
        </div>
        <slot name="header-actions" />
      </div>
    </div>
    <slot />
  </div>
</template>
