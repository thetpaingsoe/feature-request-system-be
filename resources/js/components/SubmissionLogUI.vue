<script setup lang="ts">
import { SubmissionLog } from '@/types/submission-logs';
import { FileClock, MailCheck, ArrowLeftRight, FileCheck, FileX, CircleCheckBig, CircleXIcon } from 'lucide-vue-next';

interface Props {
    submissionLog : SubmissionLog
}

defineProps<Props>();
</script>

<template>
    <!-- Create -->
    <div v-if="submissionLog.type == 'create'" class="flex my-4 w-full ">
        <div>
            <FileClock class="size-4 mx-2 mt-1" /> 
        </div>
        <div>{{ submissionLog.user_object.name }} : 
            <p class="text-gray-500 text-sm"> Created At - {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
        </div>
    </div>
    <!-- Update -->
    <div v-else-if="submissionLog.type == 'update' " class="flex my-4">
        <div>
            <FileClock class="size-4 mx-2 mt-1" /> 
        </div>
        <div>{{ submissionLog.user_object.name }} : 
            <p class="text-gray-500 text-sm"> Updated At - {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
        </div>
    </div>
    <!-- Status Change -->
    <div v-else-if="submissionLog.type == 'status_change' " class="flex my-4">
        <div>
            <ArrowLeftRight class="size-4 mx-2 mt-1" /> 
        </div> 
        <div>{{ submissionLog.user_object.name }} : 
            <p class="text-normal">Status Change From {{ submissionLog.status_change.from }} to {{ submissionLog.status_change.to }} </p>
            <p class="text-gray-500 text-sm"> at {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
        </div>
    </div>

    <!-- Feedback Message -->
    <div v-else-if="submissionLog.type == 'sugg_message' " class="flex flex-row my-4">
        <div>
            <MailCheck class="size-4 mx-2 mt-1" /> 
        </div>
        <div class="w-full">Feedback By {{ submissionLog.user_object.name }} : 
            <p class="text-gray-500 text-sm"> At {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
            <p class="text-normal mt-2 border bg-gray-100 dark:bg-gray-800 rounded py-2 px-3"> {{ submissionLog.feedback_message }} </p>
        </div>
    </div>

    <!-- Suggestion Accepted -->
    <div v-else-if="submissionLog.type == 'sugg_accept' " class="flex my-4">
        <div>
            <CircleCheckBig class="size-4 mx-2 text-green-700 mt-1 font-bold" /> 
        </div>
        <div>{{ submissionLog.user_object.name }} : 
            <p class="text-normal">Accepted Suggestion.</p>
            <p class="text-gray-500"> at {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
        </div>
    </div>

    <!-- Suggestion Rejected -->
    <div v-else-if="submissionLog.type == 'sugg_reject' " class="flex my-4">
        <div>
            <CircleXIcon class="size-4 mx-2 text-red-700 mt-1" /> 
        </div>
        <div>{{ submissionLog.user_object.name }} : 
            <p class="text-normal">Rejected Suggestion.</p>
            <p class="text-gray-500"> at {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
        </div>
    </div>

    <!-- Submission Accepted -->
    <div v-else-if="submissionLog.type == 'accepted' " class="flex my-4 w-full">
        <div>
            <FileCheck class="size-4 mx-2 text-green-700 mt-1" /> 
        </div>
        <div class="w-full">{{ submissionLog.user_object.name }} : 
            <p class="text-gray-500"> at {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
            <p class="text-normal w-full bg-green-700 text-white py-2 rounded text-center my-2">Accepted.</p>            
        </div>
    </div>

    <!-- Submission Rejected -->
    <div v-else-if="submissionLog.type == 'rejected' " class="flex my-4">
        <div>
            <FileX class="size-4 mx-2 text-red-800 mt-1" /> 
        </div>
        <div class="w-full">{{ submissionLog.user_object.name }} : 
            <p class="text-gray-500"> at {{ new Date(submissionLog.created_at).toLocaleString() }}</p>
            <p class="text-normal w-full bg-red-700 text-white py-2 rounded text-center my-2">Rejected.</p>            
        </div>
    </div>
</template>
