<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { FeatureRequest } from '@/types/feature-request';
import { Button } from '@/components/ui/button';
import { TextArea } from '@/components/ui/textarea';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';

// --- Props ---
const props = defineProps<{
  featureRequest: FeatureRequest;  
  statuses: Array<string>;
}>();

const featureRequest = computed(() => props.featureRequest );
const titleRef = ref('Title');
console.log(featureRequest.value.title);

// --- Reactive State for Editable Fields ---
const currentStatus = ref<string>(props.featureRequest ? props.featureRequest.status : '');
const currentNote = ref<string>(props.featureRequest? props.featureRequest.note : '');

// --- Available Statuses (Fallback if not passed as prop) ---
const defaultAvailableStatuses = [
  'pending',
  'approved',
  'rejected',
  'reviewed'
];
console.log(currentStatus);
const statuses = computed(() =>  props.statuses ?? defaultAvailableStatuses);

// --- Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feature Requests',
        href: '/feature-requests',
    },
    {
        title: 'Detail',
        href: '/',
    },
];

// --- Form Submission Logic ---
const handleSubmit = () => {
  router.put(
    route('feature-requests.update', { id: props.featureRequest.id }),
    {
      status: currentStatus.value,
      note: currentNote.value,      
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        alert('Feature request updated successfully!'); 
      },
      onError: (errors) => {
        console.error('Error updating feature request:', errors);
        alert('Failed to update feature request. Check console for details.'); // Replace with proper error display
      },
    }
  );
};

// --- Cancel Logic ---
const handleCancel = () => {
  router.get(route('feature-requests.index'));
};

</script>

<template>
    <Head :title="`Feature Request: ${featureRequest.title}`" ></Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        
        
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <div class=" shadow-lg rounded-lg p-6 border border-gray-200 dark:border-gray-900">
                <h3 class="text-2xl font-bold text-gray-800 mb-6 dark:text-gray-100">Feature Request Details</h3>
                
                <form @submit.prevent="handleSubmit">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Column 1: Read-only fields -->
                        <div class="space-y-4">
                            <div>
                                <Label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</Label>
                                <Input
                                    id="title"
                                    type="text"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    v-model="featureRequest.title"
                                    readonly
                                    placeholder=""
                                    class="focus-visible:ring-[0px] focus:outline-none focus:ring-0 focus:dark:border-0 cursor-not-allowed"
                                />
                            </div>

                            <div>
                                <Label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    v-model="featureRequest.email"
                                    readonly
                                    placeholder=""
                                    class="focus-visible:ring-[0px] focus:outline-none focus:ring-0 focus:dark:border-0 cursor-not-allowed"
                                />
                            </div>

                            <div>
                                <Label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</Label>
                                <TextArea
                                    id="description"
                                    type="text"
                                    required
                                    autofocus
                                    v-model="featureRequest.description"
                                    readonly
                                    placeholder=""
                                    class="h-[300px] focus-visible:ring-[0px] focus:outline-none focus:dark:border-0 cursor-not-allowed"
                                ></TextArea>
                            </div>

                            
                        </div>

                        <!-- Column 2:-->
                        <div>
                            <div>
                                <Label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</Label>
                                <select
                                    id="status"
                                    v-model="currentStatus"
                                    class="text-sm font-medium dark:bg-primary-foreground w-full px-4 py-2 dark:text-gray-200 border rounded-md focus:ring-gray-500 focus:border-gray-500 transition duration-150 ease-in-out focus:outline-none"
                                >
                                    <option v-for="statusOption in statuses" :key="statusOption" :value="statusOption">
                                        {{ statusOption }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <Label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 mt-2">Admin Note</Label>
                                <TextArea
                                    id="note"
                                    v-model="currentNote"
                                    placeholder="Add internal notes about this feature request..."
                                    class="w-full h-[385px] bg-transparent"
                                ></TextArea>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-8 flex justify-end space-x-4">
                        <Button
                            type="button"
                            @click="handleCancel"
                            class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600"
                        >
                            Cancel
                        </Button>
                        <Button type="submit" class="w-fit" tabindex="5" >
                            <!-- <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" /> :disabled="form.processing"-->
                            Update
                        </Button>
                        
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
