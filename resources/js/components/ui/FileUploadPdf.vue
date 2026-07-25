<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
  modelValue: File,
  label: String,
  error: String,
  required: Boolean,
  disabled: Boolean,
  accept: {
    type: String,
    default: '.pdf',
  },
  minSize: {
    type: Number,
    default: 100,
  },
  maxSize: {
    type: Number,
    default: 500,
  },
});

const emit = defineEmits(['update:modelValue', 'error']);

const fileInput = ref(null);
const isDragging = ref(false);
const uploadProgress = ref(0);
const validationError = ref('');

const fileInfo = computed(() => {
  if (!props.modelValue) return null;
  
  return {
    name: props.modelValue.name,
    size: (props.modelValue.size / 1024).toFixed(2) + ' KB',
    type: props.modelValue.type,
  };
});

const validateFile = (file) => {
  if (!file) return 'Please select a file';
  
  if (!file.type.includes('pdf')) {
    return 'Only PDF files are allowed';
  }
  
  const sizeKB = file.size / 1024;
  
  if (sizeKB < props.minSize) {
    return `File size must be at least ${props.minSize}KB`;
  }
  
  if (sizeKB > props.maxSize) {
    return `File size must not exceed ${props.maxSize}KB`;
  }
  
  return null;
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  processFile(file);
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  processFile(file);
};

const processFile = (file) => {
  if (!file) return;
  
  const error = validateFile(file);
  
  if (error) {
    validationError.value = error;
    emit('error', error);
    return;
  }
  
  validationError.value = '';
  uploadProgress.value = 100;
  emit('update:modelValue', file);
};

const handleDragOver = (event) => {
  isDragging.value = true;
};

const handleDragLeave = () => {
  isDragging.value = false;
};

const removeFile = () => {
  emit('update:modelValue', null);
  validationError.value = '';
  uploadProgress.value = 0;
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const triggerFileInput = () => {
  if (!props.disabled) {
    fileInput.value?.click();
  }
};
</script>

<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div
      @click="triggerFileInput"
      @drop.prevent="handleDrop"
      @dragover.prevent="handleDragOver"
      @dragleave="handleDragLeave"
      class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer transition-colors"
      :class="{
        'border-primary-400 bg-primary-50': isDragging,
        'border-gray-300 hover:border-gray-400': !isDragging && !error && !validationError,
        'border-red-300 bg-red-50': error || validationError,
        'opacity-50 cursor-not-allowed': disabled,
      }"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        :disabled="disabled"
        @change="handleFileSelect"
        class="hidden"
      />
      
      <div v-if="!fileInfo">
        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
          <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <p class="mt-2 text-sm text-gray-600">
          <span class="font-medium text-primary-600">Click to upload</span> or drag and drop
        </p>
        <p class="mt-1 text-xs text-gray-500">
          PDF only, {{ minSize }}KB - {{ maxSize }}KB
        </p>
      </div>
      
      <div v-else class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <svg class="h-8 w-8 text-red-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
          </svg>
          <div class="text-left">
            <p class="text-sm font-medium text-gray-900">{{ fileInfo.name }}</p>
            <p class="text-xs text-gray-500">{{ fileInfo.size }}</p>
          </div>
        </div>
        <button
          @click.stop="removeFile"
          type="button"
          class="text-gray-400 hover:text-red-600 transition-colors"
        >
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>
    </div>
    
    <p v-if="error || validationError" class="text-sm text-red-600">
      {{ error || validationError }}
    </p>
  </div>
</template>
