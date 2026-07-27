<script setup>
import { useToast } from '@/composables/useToast';
import { CheckCircle2, XCircle, AlertTriangle, Info, X } from 'lucide-vue-next';

const { toasts, dismissToast } = useToast();

const getIcon = (type) => {
  const icons = {
    success: CheckCircle2,
    error: XCircle,
    warning: AlertTriangle,
    info: Info,
  };
  return icons[type] || Info;
};

const getIconColor = (type) => {
  const colors = {
    success: 'text-success-500',
    error: 'text-danger-500',
    warning: 'text-warning-500',
    info: 'text-primary-500',
  };
  return colors[type] || 'text-primary-500';
};

const getBorderColor = (type) => {
  const colors = {
    success: 'border-success-500',
    error: 'border-danger-500',
    warning: 'border-warning-500',
    info: 'border-primary-500',
  };
  return colors[type] || 'border-primary-500';
};

const getProgressColor = (type) => {
  const colors = {
    success: 'bg-success-500',
    error: 'bg-danger-500',
    warning: 'bg-warning-500',
    info: 'bg-primary-500',
  };
  return colors[type] || 'bg-primary-500';
};
</script>

<template>
  <div class="fixed top-4 right-4 z-50 flex flex-col gap-3 pointer-events-none">
    <TransitionGroup
      name="toast"
      tag="div"
      class="flex flex-col gap-3"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="w-96 max-w-[90vw] bg-white rounded-lg shadow-lg pointer-events-auto overflow-hidden"
        :class="getBorderColor(toast.type)"
      >
        <div class="p-4">
          <div class="flex items-start gap-3">
            <div class="flex-shrink-0 mt-0.5">
              <component
                :is="getIcon(toast.type)"
                :size="20"
                :class="getIconColor(toast.type)"
              />
            </div>

            <div class="flex-1 min-w-0">
              <h4 class="font-semibold text-slate-900 text-sm">
                {{ toast.title }}
              </h4>
              <p v-if="toast.message" class="text-sm text-slate-600 mt-1">
                {{ toast.message }}
              </p>
            </div>

            <button
              @click="dismissToast(toast.id)"
              class="flex-shrink-0 p-1 text-slate-400 hover:text-slate-600 rounded transition-colors"
              type="button"
            >
              <X :size="16" />
            </button>
          </div>
        </div>

        <div
          v-if="toast.duration > 0"
          class="h-1 bg-slate-100 overflow-hidden"
        >
          <div
            class="h-full toast-progress"
            :class="getProgressColor(toast.type)"
            :style="{ animationDuration: `${toast.duration}ms` }"
          />
        </div>
      </div>
    </TransitionGroup>
  </div>
</template>

<style scoped>
.toast-enter-active {
  animation: toast-slide-in 0.3s ease-out;
}

.toast-leave-active {
  animation: toast-slide-out 0.2s ease-in;
}

.toast-move {
  transition: all 0.3s ease;
}

@keyframes toast-slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes toast-slide-out {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

.toast-progress {
  animation-name: progress-shrink;
  animation-timing-function: linear;
  animation-fill-mode: forwards;
}

@keyframes progress-shrink {
  from {
    width: 100%;
  }
  to {
    width: 0%;
  }
}
</style>
