<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, nextTick } from 'vue';
import { Submission } from '@/types/submissions';
import { Button } from '@/components/ui/button';
// import { TextArea } from '@/components/ui/textarea';
// import { Input } from '@/components/ui/input';
// import { Label } from '@/components/ui/label';
import { LoaderCircle } from 'lucide-vue-next';
// import HeadingSmall from '@/components/HeadingSmall.vue';
import RHeadingSmall from '@/components/RHeadingSmall.vue';
import { SubmissionLog, SubmissionLogPagination } from '@/types/submission-logs';
import SubmissionLogUI from '@/components/SubmissionLogUI.vue';
import TextArea from '@/components/ui/textarea/TextArea.vue';

// --- Props ---
const props = defineProps<{
    submission: Submission;  
    statuses: Array<string>;
    submissionLogsPagination : SubmissionLogPagination;
  
}>();

console.log(props.submissionLogsPagination);
const submissionLogsPagination = computed(() => props.submissionLogsPagination ?? []);
const submissionLogs = ref<SubmissionLog[]>( props.submissionLogsPagination ? props.submissionLogsPagination.data : []); 
console.log(submissionLogs.value.reverse());

computed(() => {
  // Create a shallow copy of the array before reversing to avoid mutating the original
  return [...submissionLogs.value].reverse();
});


const submission = computed(() => props.submission );
const processing = ref(false);

// --- Reactive State for Editable Fields ---
const currentStatus = ref<string>(props.submission ? props.submission.status : '');
const currentNote = ref<string>('');

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

const triggerInertiaVisit = (currentPage: number ) => {
    
    const params: Record<string, any> = {
        page: currentPage ,
        per_page: submissionLogsPagination.value.per_page * 2,
    };

    router.get(route('submissions.edit', { id: submission.value.id, ...params }),
    {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['submissionLogsPagination', 'flash'],
    }
)};

// --- Form Submission Logic ---
const handleStatusChange = (options?: { status?: string}) => {
    processing.value = true;
    // console.log(forceStatus);
    if(options?.status) {
        currentStatus.value = options?.status;
    }

    router.put(
        route('submissions.update-status', { id: props.submission.id }),
        {
            status: currentStatus.value,
            note: currentNote.value,      
        },
        {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
                // processing.value = false;
                // alert('Submission updated successfully!'); 
                // router.reload()
            },
            onError: (errors) => {
                // processing.value = false;
                console.error('Error updating submission:', errors);
                // alert('Failed to update submission. Check console for details.'); // Replace with proper error display
            },
        }
    );
};

// // --- Cancel Logic ---
// const handleCancel = () => {
//   router.get(route('submissions.index'));
// };

// router.on('finish', (event) => {  
//     if(col2Ref.value != null)
//         console.log(col2Ref.value.scrollHeight);
//     if (! event.detail.visit.url.toString().includes("preserveState") && col2Ref.value != null) {
//         col2Ref.value.scrollTop = col2Ref.value.scrollHeight;
//     }
// });

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
                                :description="submission.shareholders?.map(j => `${j.name ?? '-'}\n ${j.email ?? '-'}\n ${j.percentage ?? '-'}%`).join('\n\n') ?? ''" 
                                class="mt-4"
                                style="white-space: pre-wrap;"
                            />
                            <hr class="mt-4"/>
                            <RHeadingSmall 
                                title="Beneficial Owner" 
                                :description="submission.beneficial_owners?.map(j => `${j.name ?? '-'}\n${j.relationship ?? '-'}`).join('\n\n') ?? ''" 
                                class="mt-4"
                                style="white-space: pre-wrap;"
                            />
                            <hr class="mt-4"/>
                            <RHeadingSmall 
                                title="Directors" 
                                :description="submission.directors?.map(j => `${j.name ?? '-'}\n${j.email ?? '-'}\n${j.position ?? '-'}`).join('\n\n') ?? ''" 
                                class="mt-4"
                                style="white-space: pre-wrap;"
                            />                    
                            <div class="mt-20" />
                        </div>
                    </div>
                </div>

                <!-- Column 2 -->
                <div ref="col2Ref" class="md:w-1/3 md:overflow-y-auto p-4 border border-dashed border-gray-400 dark:border-gray-800 rounded">
                    
                    <div class="space-y-4">
                        <div class="w-full ">

                            <div v-if="submissionLogsPagination.current_page < submissionLogsPagination.last_page">
                                <p @click="triggerInertiaVisit(submissionLogsPagination.current_page)" class=" cursor-pointer text-center w-full font-bold underline ">Load More ...</p>
                            </div>
                            <div class="w-full" v-for="s in submissionLogs " :key="s.id">
                                <SubmissionLogUI :submission-log="s" />
                            </div>

                            <div class="flex flex-col items-center justify-center" v-if="submission.status == 'pending'">
                                <p class="text-sm text-muted-foreground mt-12"> Are you ready to start review this submission ? </p>        

                                <Button type="submit" class=" w-48 mt-4 " tabindex="5" :disabled="processing" @click="handleStatusChange({status : 'reviewing'})">
                                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" /> 
                                    Start Review
                                </Button>
                            </div>

                            <div class="flex flex-col items-center justify-center mx-2" v-else-if="submission.status == 'reviewing' || submission.status == 'feedback'">
                                
                                <div class="w-full">
                                    <hr class="mt-4 mb-4"/>
                                    <Label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</Label>
                                    <select
                                        id="status"
                                        v-model="currentStatus"
                                        class=" text-sm font-medium dark:bg-primary-foreground w-full px-4 py-2 dark:text-gray-200 border rounded-md focus:ring-gray-500 focus:border-gray-500 transition duration-150 ease-in-out focus:outline-none"
                                    >
                                        <option v-for="statusOption in statuses" :key="statusOption" :value="statusOption">
                                            {{ statusOption }}
                                        </option>
                                    </select>
                                </div>

                                <div class="w-full" v-if="currentStatus == 'feedback'">
                                    <Label for="note" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 mt-2">What is your feedback ? </Label>
                                    
                                    <TextArea
                                        id="note"   
                                        v-model="currentNote"                                     
                                        placeholder="Please write here ..."
                                        class="w-full h-[200px] bg-transparent"
                                    ></TextArea>
                                </div>

                                <Button type="ghost" variant="outline" class=" w-full mt-4 " tabindex="5" :disabled="processing" @click="handleStatusChange">
                                    <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" /> 
                                    Submit
                                </Button>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AppLayout>
</template>
