<script setup>
import { ref, watch } from 'vue';
import { X, Plus } from 'lucide-vue-next';
import BaseButton from '@/components/ui/BaseButton.vue';

const props = defineProps({
    modelValue: {
        type: Object,
        default: () => ({}),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue']);

const metadataRows = ref([]);

const initializeRows = (metadata) => {
    if (!metadata || typeof metadata !== 'object') {
        metadataRows.value = [];
        return;
    }
    
    metadataRows.value = Object.entries(metadata).map(([key, value]) => ({
        key,
        value: String(value),
        id: Math.random().toString(36).substr(2, 9),
    }));
};

watch(() => props.modelValue, (newValue) => {
    if (!metadataRows.value.length && newValue) {
        initializeRows(newValue);
    }
}, { immediate: true });

const addRow = () => {
    metadataRows.value.push({
        key: '',
        value: '',
        id: Math.random().toString(36).substr(2, 9),
    });
};

const removeRow = (id) => {
    metadataRows.value = metadataRows.value.filter(row => row.id !== id);
    emitUpdate();
};

const emitUpdate = () => {
    const metadata = {};
    metadataRows.value.forEach(row => {
        if (row.key.trim()) {
            metadata[row.key.trim()] = row.value;
        }
    });
    emit('update:modelValue', Object.keys(metadata).length > 0 ? metadata : null);
};

const handleKeyChange = (id, newKey) => {
    const row = metadataRows.value.find(r => r.id === id);
    if (row) {
        row.key = newKey;
        emitUpdate();
    }
};

const handleValueChange = (id, newValue) => {
    const row = metadataRows.value.find(r => r.id === id);
    if (row) {
        row.value = newValue;
        emitUpdate();
    }
};
</script>

<template>
    <div class="space-y-3">
        <div class="flex items-center justify-between">
            <label class="block text-sm font-medium text-slate-700">
                Metadata
                <span class="text-xs text-slate-500 font-normal ml-2">(Optional key-value pairs)</span>
            </label>
            <BaseButton
                type="button"
                variant="ghost"
                size="sm"
                @click="addRow"
                :disabled="disabled"
            >
                <Plus :size="16" />
                Tambah Field
            </BaseButton>
        </div>

        <div v-if="metadataRows.length === 0" class="text-sm text-slate-500 italic p-4 border border-dashed border-slate-300 rounded-lg text-center">
            Tidak ada metadata. Klik "Tambah Field" untuk menambahkan.
        </div>

        <div v-else class="space-y-2">
            <div
                v-for="row in metadataRows"
                :key="row.id"
                class="flex items-center gap-2"
            >
                <input
                    :value="row.key"
                    @input="handleKeyChange(row.id, $event.target.value)"
                    type="text"
                    placeholder="Key"
                    :disabled="disabled"
                    class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-slate-100 disabled:cursor-not-allowed transition-all"
                />
                <input
                    :value="row.value"
                    @input="handleValueChange(row.id, $event.target.value)"
                    type="text"
                    placeholder="Value"
                    :disabled="disabled"
                    class="flex-1 px-3 py-2 text-sm border border-slate-300 rounded-lg focus:outline-none focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500 disabled:bg-slate-100 disabled:cursor-not-allowed transition-all"
                />
                <button
                    type="button"
                    @click="removeRow(row.id)"
                    :disabled="disabled"
                    class="p-2 text-slate-600 hover:text-danger-600 hover:bg-danger-50 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Hapus"
                >
                    <X :size="16" />
                </button>
            </div>
        </div>
    </div>
</template>
