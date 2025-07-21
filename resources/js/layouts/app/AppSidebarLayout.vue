<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import ToastNotification from '@/components/ToastNotification.vue';
import type { BreadcrumbItemType } from '@/types';
import { ToastNotificationData } from '@/types/toast-types';
import { usePage } from '@inertiajs/vue3';
import { ToastProvider, ToastViewport } from 'reka-ui';
import { ref, watch } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// --- Toast Notification Logic ---
const page = usePage();
const toastObj = ref<ToastNotificationData | null>();

// Watch for flash messages from Inertia props
watch(() => page.props.flash, (flash) => {
    if(flash != null) {
        toastObj.value = flash as ToastNotificationData;
        setTimeout(() => {
            toastObj.value = null
        }, toastObj.value.duration ?? 3000);
    }
}, { deep: true, immediate : true});

</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>

        <!-- Reka UI Toast Provider and Viewport (Global for all toasts) -->
        <ToastProvider>
            <ToastNotification
                v-if="toastObj"
                :toastObj = "toastObj"                                
            />            
            <ToastViewport class="fixed bottom-4 right-4 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:flex-col md:max-w-[420px]" />
        </ToastProvider>
    </AppShell>
</template>
