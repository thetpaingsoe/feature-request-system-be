<script setup lang="ts">
import { ToastNotificationData } from '@/types/toast-types';
import {
    ToastAction,
    ToastDescription,
    ToastRoot,
    ToastTitle,
} from 'reka-ui';
import { ref, watch } from 'vue';

const props = defineProps<{
    toastObj: ToastNotificationData
}>();

const open = ref(false);
const timer = ref<ReturnType<typeof setTimeout> | null>(null);

// Watch for changes in message or type to trigger toast
watch(() => props.toastObj.message, (newMessage) => {
    if (newMessage) {
        open.value = true;
        if (timer.value) {
            clearTimeout(timer.value);
        }
        timer.value = setTimeout(() => {
            open.value = false;
        }, props.toastObj.duration || 3000); // Default to 3 seconds
    } else {
        open.value = false;
    }
}, { immediate: true }); 

// Clear timer if component unmounts
import { onUnmounted } from 'vue';
onUnmounted(() => {
  if (timer.value) {
    clearTimeout(timer.value);
  }
});

// Helper to determine toast variant based on type
const getVariant = (type: string) => {
  switch (type) {
    case 'success': return 'default';
    case 'error': return 'destructive';
    case 'info': return 'default';
    default: return 'default';
  }
};

// Helper to get title based on type
const getTitle = (title: string, type: string) => {

    if(title != null && title != '') return title;

    switch (type) {
        case 'success': return 'Success!';
        case 'error': return 'Error!';
        case 'error': return 'Warning!';
        case 'info': return 'Info!';
        default: return 'Notification';
    }
};

</script>

<template>
  <ToastRoot
      v-model:open="open"
      variant="default"
      class=" dark:bg-gray-900 rounded-lg shadow-sm border p-[15px] grid [grid-template-areas:_'title_action'_'description_action'] grid-cols-[auto_max-content] gap-x-[15px] items-center data-[state=open]:animate-slideIn data-[state=closed]:animate-hide data-[swipe=move]:translate-x-[var(--reka-toast-swipe-move-x)] data-[swipe=cancel]:translate-x-0 data-[swipe=cancel]:transition-[transform_200ms_ease-out] data-[swipe=end]:animate-swipeOut"
      :class="{
        'bg-green-500 text-white dark:bg-green-900': props.toastObj.type === 'success',
        'bg-red-500 text-white dark:bg-red-900': props.toastObj.type === 'error',
        'bg-yellow-600 text-white dark:bg-yellow-600': props.toastObj.type === 'warning',
        'bg-blue-500 text-white dark:bg-blue-900': props.toastObj.type === 'info',
    }"
    >
      <ToastTitle class="[grid-area:_title] mb-[5px] font-medium text-slate12 text-sm">
        {{ getTitle(props.toastObj.title ?? '', props.toastObj.type) }}        
      </ToastTitle>
      <ToastDescription as-child>
        <div class="[grid-area:_description] m-0 text-slate11 text-xs leading-[1.3]">
            {{ props.toastObj.message }}          
        </div>
      </ToastDescription>
      <!-- <ToastAction
        class="[grid-area:_action]"
        as-child
        alt-text="Goto schedule to undo"
      > -->
        <!-- <button class="inline-flex items-center justify-center rounded-md font-medium text-xs px-[10px] leading-[25px] h-[25px] bg-green2 text-green11 shadow-[inset_0_0_0_1px] shadow-green7 hover:shadow-[inset_0_0_0_1px] hover:shadow-green8 focus:shadow-[0_0_0_2px] focus:shadow-green8">
          Undo
        </button> -->
      <!-- </ToastAction> -->
    </ToastRoot>
</template>
