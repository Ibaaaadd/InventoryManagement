<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { Search, Check, ChevronDown, Loader2, XCircle } from 'lucide-vue-next';

const props = defineProps({
  options: {
    type: Array,
    required: true,
  },
  modelValue: [String, Number],
  placeholder: {
    type: String,
    default: 'Select an option',
  },
  label: String,
  error: String,
  required: Boolean,
  disabled: Boolean,
  loading: Boolean,
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);
const highlightedIndex = ref(-1);

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  
  const query = searchQuery.value.toLowerCase();
  return props.options.filter(option => 
    option.label.toLowerCase().includes(query)
  );
});

const selectedOption = computed(() => {
  return props.options.find(opt => opt.value === props.modelValue);
});

const displayValue = computed(() => {
  return selectedOption.value?.label || props.placeholder;
});

const toggleDropdown = () => {
  if (props.disabled || props.loading) return;
  
  isOpen.value = !isOpen.value;
  
  if (isOpen.value) {
    searchQuery.value = '';
    highlightedIndex.value = -1;
    setTimeout(() => {
      searchInputRef.value?.focus();
    }, 50);
  }
};

const selectOption = (option) => {
  emit('update:modelValue', option.value);
  isOpen.value = false;
  searchQuery.value = '';
  highlightedIndex.value = -1;
};

const handleClickOutside = (event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
    isOpen.value = false;
    searchQuery.value = '';
  }
};

const handleKeydown = (event) => {
  if (!isOpen.value) return;
  
  switch (event.key) {
    case 'ArrowDown':
      event.preventDefault();
      highlightedIndex.value = Math.min(
        highlightedIndex.value + 1,
        filteredOptions.value.length - 1
      );
      break;
    case 'ArrowUp':
      event.preventDefault();
      highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
      break;
    case 'Enter':
      event.preventDefault();
      if (highlightedIndex.value >= 0 && filteredOptions.value[highlightedIndex.value]) {
        selectOption(filteredOptions.value[highlightedIndex.value]);
      }
      break;
    case 'Escape':
      event.preventDefault();
      isOpen.value = false;
      searchQuery.value = '';
      break;
  }
};

watch(filteredOptions, () => {
  highlightedIndex.value = -1;
});

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div class="space-y-1.5" ref="dropdownRef">
    <label v-if="label" class="block text-sm font-medium text-slate-700">
      {{ label }}
      <span v-if="required" class="text-danger-600">*</span>
    </label>

    <div class="relative">
      <button
        type="button"
        @click="toggleDropdown"
        :disabled="disabled || loading"
        class="w-full px-3 py-2.5 text-sm text-left border rounded-lg focus:outline-none transition-colors duration-150 flex items-center justify-between gap-2 disabled:bg-slate-50 disabled:cursor-not-allowed disabled:text-slate-500"
        :class="{
          'border-danger-300 ring-2 ring-danger-500/20 focus:ring-danger-500/20 bg-danger-50/50': error,
          'border-slate-300 focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500': !error && !disabled,
          'bg-slate-50': disabled,
        }"
      >
        <span 
          class="truncate"
          :class="!selectedOption ? 'text-slate-500' : 'text-slate-900'"
        >
          {{ displayValue }}
        </span>
        <Loader2 v-if="loading" :size="16" class="animate-spin text-slate-400 flex-shrink-0" />
        <ChevronDown 
          v-else
          :size="16" 
          class="text-slate-400 transition-transform flex-shrink-0"
          :class="{ 'rotate-180': isOpen }"
        />
      </button>

      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div
          v-if="isOpen"
          class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden"
        >
          <div class="p-2 border-b border-slate-100">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" :size="16" />
              <input
                ref="searchInputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Search..."
                @keydown="handleKeydown"
                class="w-full pl-9 pr-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none"
              />
            </div>
          </div>

          <div class="max-h-60 overflow-y-auto">
            <div
              v-if="filteredOptions.length === 0"
              class="px-4 py-8 text-center text-sm text-slate-500"
            >
              No results found
            </div>
            <button
              v-else
              v-for="(option, index) in filteredOptions"
              :key="option.value"
              type="button"
              @click="selectOption(option)"
              class="w-full px-4 py-2.5 text-sm text-left hover:bg-slate-50 transition-colors flex items-center justify-between gap-2"
              :class="{
                'bg-primary-50 text-primary-700': modelValue === option.value,
                'bg-slate-100': highlightedIndex === index && modelValue !== option.value,
              }"
            >
              <span class="truncate">{{ option.label }}</span>
              <Check 
                v-if="modelValue === option.value"
                :size="16" 
                class="text-primary-600 flex-shrink-0"
              />
            </button>
          </div>
        </div>
      </transition>
    </div>

    <p v-if="error" class="text-xs text-danger-600 flex items-center gap-1">
      <XCircle :size="16" />
      {{ error }}
    </p>
  </div>
</template>
