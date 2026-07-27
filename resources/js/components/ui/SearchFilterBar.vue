<script setup>
import { ChevronDown } from "lucide-vue-next";

defineProps({
    searchQuery: {
        type: String,
        default: "",
    },
    placeholder: {
        type: String,
        default: "Search...",
    },
    sortOptions: {
        type: Array,
        default: () => [],
    },
    sortValue: String,
});

const emit = defineEmits([
    "update:searchQuery",
    "update:sortValue",
    "search",
    "clear",
]);

const handleSearch = (value) => {
    emit("update:searchQuery", value);
    emit("search", value);
};

const handleSort = (value) => {
    emit("update:sortValue", value);
};

const handleClear = () => {
    emit("update:searchQuery", "");
    emit("update:sortValue", "");
    emit("clear");
};
</script>

<template>
    <div class="flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <div class="relative">
                <input
                    :value="searchQuery"
                    @input="handleSearch($event.target.value)"
                    type="text"
                    :placeholder="placeholder"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-1/2 focus:ring-primary-500 focus:border-primary-500"
                />
                <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                    <svg
                        class="h-5 w-5 text-gray-400"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                        />
                    </svg>
                </div>
            </div>
        </div>

        <div v-if="sortOptions.length > 0" class="sm:w-64">
            <div class="relative">
                <select
                    :value="sortValue"
                    @change="handleSort($event.target.value)"
                    class="w-full appearance-none pl-3 pr-10 py-2 text-slate-700 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer"
                >
                    <option value="">Sort by...</option>
                    <option
                        v-for="option in sortOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <ChevronDown
                    :size="18"
                    class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400"
                />
            </div>
        </div>

        <div class="flex gap-2">
            <slot name="filters" />

            <button
                v-if="searchQuery || sortValue"
                @click="handleClear"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                Clear
            </button>
        </div>
    </div>
</template>
