<script setup>
defineProps({
  title: String,
  shadow: {
    type: Boolean,
    default: true,
  },
  padding: {
    type: Boolean,
    default: true,
  },
});
</script>

<template>
  <div
    class="bg-white rounded-lg border border-gray-200"
    :class="{
      'shadow-sm': shadow,
      'p-6': padding,
    }"
  >
    <div v-if="title" class="mb-4 text-lg font-semibold text-gray-900">
      {{ title }}
    </div>
    <slot />
  </div>
</template>
