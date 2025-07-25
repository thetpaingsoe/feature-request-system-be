<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import { Submission } from '@/types/submissions';
import { Button } from '@/components/ui/button';
// import { TextArea } from '@/components/ui/textarea';
// import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';
// import HeadingSmall from '@/components/HeadingSmall.vue';
import RHeadingSmall from '@/components/RHeadingSmall.vue';

// --- Props ---
const props = defineProps<{
  submission: Submission;  
  statuses: Array<string>;
}>();

const submission = computed(() => props.submission );
const processing = ref(false);

// --- Reactive State for Editable Fields ---
// const currentStatus = ref<string>(props.submission ? props.submission.status : '');
// const currentNote = ref<string>(props.submission? props.submission.note : '');

// --- Available Statuses (Fallback if not passed as prop) ---
// const defaultAvailableStatuses = [
//   'pending',
//   'approved',
//   'rejected',
//   'reviewed'
// ];

// const statuses = computed(() =>  props.statuses ?? defaultAvailableStatuses);

// --- Breadcrumbs ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Submissions',
        href: '/submissions',
    },
    {
        title: 'Detail',
        href: '/',
    },
];

// --- Form Submission Logic ---
// const handleSubmit = () => {
//     processing.value = true;
//     router.put(
//         route('submissions.update', { id: props.submission.id }),
//         {
//             status: currentStatus.value,
//             // note: currentNote.value,      
//         },
//         {
//             preserveScroll: true,
//             onSuccess: () => {
//                 // processing.value = false;
//                 // alert('Submission updated successfully!'); 
//             },
//             onError: (errors) => {
//                 // processing.value = false;
//                 console.error('Error updating submission:', errors);
//                 // alert('Failed to update submission. Check console for details.'); // Replace with proper error display
//             },
//         }
//     );
// };

// // --- Cancel Logic ---
// const handleCancel = () => {
//   router.get(route('submissions.index'));
// };

const col2Ref = ref<HTMLElement | null>(null);

onMounted(() => {
  // Scroll to bottom after DOM is ready
  nextTick(() => {
    if (col2Ref.value) {
      col2Ref.value.scrollTop = col2Ref.value.scrollHeight;
    }
  });
});

</script>

<template>
    <Head :title="`Submission: ${submission.company_name}`" ></Head>

    <AppLayout :breadcrumbs="breadcrumbs" class="h-screen overflow-hidden">
        
       <div class="flex flex-col md:h-full md:overflow-hidden py-8 px-6">
            <!-- Title Bar -->
            <h3 class="text-2xl font-bold text-gray-800 mb-6 dark:text-gray-100">Submission : {{ submission.company_name }}</h3>

            <!-- Columns Container -->
            <div class="flex flex-col md:flex-row md:flex-1 md:overflow-hidden gap-6">
                <!-- Column 1 -->
                <div class="md:w-2/3 overflow-y-auto p-4 border border-gray-300 rounded dark:border-gray-700">
                    <div class="space-y-4">
                        <div>

                            <!-- {{ submission }} -->
                            <RHeadingSmall title="Full Name" :description="submission.full_name" />
                            <RHeadingSmall title="Email" :description="submission.email" class="mt-4" />
                            <hr class="mt-4"/>
                            <RHeadingSmall title="Company Name" :description="submission.company_name" class="mt-4"/>
                            <RHeadingSmall title="Alternative Company Name" :description="submission.alternative_company_name ?? ''" class="mt-4"/>
                            <RHeadingSmall title="Jurisdiction of Operation" :description="submission.jurisdiction_of_operation?.name ?? ''" class="mt-4"/>
                            <RHeadingSmall
                                title="Jurisdiction of Operation"
                                :description="submission.target_jurisdiction_names?.map(j => j.name).join(', ') ?? ''"
                                class="mt-4"
                            />
                            <RHeadingSmall title="Number of Shares" :description="submission.number_of_shares?.toString() ?? ''" class="mt-4"/>
                            <RHeadingSmall title="Number of Issued Shares" :description="submission.number_of_issued_shares?.toString() ?? ''" class="mt-4"/>                        
                            <RHeadingSmall 
                                title="Are All Share Issued?" 
                                :description="submission.are_all_shares_issued ? 'YES' : 'NO'" 
                                class="mt-4"
                            />
                            <RHeadingSmall 
                                title="Value Per Shares" 
                                :description="`${submission.share_value?.currency ?? ''} ${submission.share_value?.amount ?? ''}`.trim()" 
                                class="mt-4"
                            />
                            <hr class="mt-4"/>
                            <RHeadingSmall 
                                title="Shareholders" 
                                :description="submission.shareholders?.map(j => `${j.name ?? ''}, ${j.name ?? ''}, , ${j.percentage ?? ''}%`).join('\n') ?? ''" 
                                class="mt-4"
                                style="white-space: pre-wrap;"
                            />
                            <RHeadingSmall 
                                title="Beneficial Owner" 
                                :description="submission.beneficial_owners?.map(j => `${j.name ?? ''}, ${j.relationship ?? ''}`).join('\n') ?? ''" 
                                class="mt-4"
                                style="white-space: pre-wrap;"
                            />
                            <RHeadingSmall 
                                title="Directors" 
                                :description="submission.directors?.map(j => `${j.name ?? ''}, ${j.email ?? ''}, ${j.position ?? ''}`).join('\n') ?? ''" 
                                class="mt-4"
                                style="white-space: pre-wrap;"
                            />                    
                            <div class="mt-40" />
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div ref="col2Ref" class="md:w-1/3 md:overflow-y-auto p-4 border border-dashed border-gray-800 rounded">
                    <div class="space-y-4">
                        <div class="w-full flex flex-col items-center justify-center">

                            <p class="text-sm text-muted-foreground mt-12"> Are you ready to start review this submission ? </p>        

                            <Button type="submit" class=" w-48 mt-4 " tabindex="5" :disabled="processing">
                                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" /> 
                                Start Review
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
