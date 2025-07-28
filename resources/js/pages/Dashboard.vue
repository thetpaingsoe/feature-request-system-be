<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm} from '@inertiajs/vue3';
import { ref, computed, h, watch, onMounted } from 'vue';
import Label from '@/components/ui/label/Label.vue';
import { useDebounceFn } from '@vueuse/core';
import '@vuepic/vue-datepicker/dist/main.css'

import {
    useVueTable,
    getCoreRowModel,
    getFilteredRowModel,
    FlexRender,
    createColumnHelper,
} from '@tanstack/vue-table';

import { 
    SubmissionLog, 
    SubmissionLogPagination, 
} from '@/types/submission-logs';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { InertiaFilters, InertiaSorting, Submission } from '@/types/submissions';
import { FileSearch2, MailPlus, MessageSquare, PackageCheck } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// --- Pagination Controls ---
const pageSizes: number[] = [10, 20, 50, 100];

// --- Handling Props ---
const props = defineProps<{
  submissionLogsPagination: SubmissionLogPagination; 
  filters: InertiaFilters; 
  sorting: InertiaSorting; 
  counts : {
    pending : string,
    reviewing : string,
    feedback : string,
    total : string,
  }
}>();

const submissionLogsPagination = computed(() => props.submissionLogsPagination ?? []);
const submissionLogs = ref<SubmissionLog[]>(props.submissionLogsPagination ? props.submissionLogsPagination.data : []); // Use the 'data' array from the pagination prop

// --- Table State ---
const filterTypeData = props.filters ?? ref<any[]>([]);
const sortingRef = ref<any[]>([]);
const globalFilter = ref<string>(filterTypeData.search || '');
const selectedStatus = ref<string>(filterTypeData.status || 'All');
const dateFilterStart = ref<string>('');
const dateFilterEnd = ref<string>('');
const dateFilter = ref([props.filters.date_start || '', props.filters.date_end || '']);
const isDark = ref(false)

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark')
})

const pagination = ref({
    pageIndex: submissionLogsPagination.value.current_page - 1, // Initialize from props (0-indexed)
    pageSize: submissionLogsPagination.value.per_page, // Initialize from props
});


// --- Update table data when there is changes ---
watch(() => props.submissionLogsPagination, (newCountriesData) => {        
  submissionLogs.value = newCountriesData ? newCountriesData.data : [];
  pagination.value.pageIndex = newCountriesData ? newCountriesData.current_page : 1;
}, { deep: true, immediate: true }); 
 
// --- Intertia Request ---
const triggerInertiaVisit = (options?: { resetPageIndex?: boolean; newPageIndex?: number }) => {
    if (options?.resetPageIndex) {
        pagination.value.pageIndex = 1; 
    } else if (options?.newPageIndex !== undefined) {
        pagination.value.pageIndex = options.newPageIndex; // Set to a specific new page index
    }

    const sortBy = sortingRef.value.length > 0 ? sortingRef.value[0].id : null;
    const sortDirection = sortingRef.value.length > 0 ? (sortingRef.value[0].desc ? 'desc' : 'asc') : null;
    
    const params: Record<string, any> = {
        page: pagination.value.pageIndex , // Page and per_page are always sent
        per_page: pagination.value.pageSize,
    };

    if (globalFilter.value) params.search = globalFilter.value;
    if (selectedStatus.value !== 'All') params.status = selectedStatus.value;
    if (dateFilterStart.value) params.date_start = dateFilterStart.value;
    if (dateFilterEnd.value) params.date_end = dateFilterEnd.value;
    if (sortBy) { 
        params.sort_by = sortBy;
        params.sort_direction = sortDirection;
    }

    router.get(route('dashboard'), params,
    {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['submissionLogsPagination', 'filters', 'sorting', 'flash'],
    }
)};

// -- Filter Sections --
// const filterSection = ref(false);
// function toggleFilterSection() {
//     filterSection.value = !filterSection.value;
// }

// --- Keyword Search ---
watch(
    globalFilter,
    useDebounceFn(() => {
        triggerInertiaVisit({ resetPageIndex: true })    
    }, 500)
)

// --- Sorting ---
watch(sortingRef, () => {
    triggerInertiaVisit({ resetPageIndex: true });
}, { deep: true }); 

// --- Status ----
watch(selectedStatus, () => {
    triggerInertiaVisit({ resetPageIndex: true });
});

// --- Date Range ----
watch(dateFilter, () => {
    if (dateFilter.value != null && dateFilter.value.length > 1) {
        const tempStartDate = new Date(dateFilter.value[0]); 
        tempStartDate.setHours(0, 0, 0, 0); 
        dateFilterStart.value = tempStartDate.toISOString();

        const tempEndDate = new Date(dateFilter.value[1]);
        tempEndDate.setHours(23, 59, 59, 999); 
        dateFilterEnd.value = tempEndDate.toISOString();        

        triggerInertiaVisit({ resetPageIndex: true });
    }
});
// function dateFilterCleared() {
//     dateFilter.value = [];
//     dateFilterStart.value = "";
//     dateFilterEnd.value = "";
//     triggerInertiaVisit({ resetPageIndex: true });
// }

// @todo --- Action Handlers ---
// const handleEdit = (id: number): void => {
  
// //   router.get(route('submissionLogs.edit', { id: id }));
// };

const isDeleteDialogOpen = ref(false);
const countryNameToDelete = ref<string>('');

const form = useForm({
    id: 0,
});
// const handleDelete = (e: Event) => {
    // e.preventDefault();

    // const sortBy = sortingRef.value.length > 0 ? sortingRef.value[0].id : null;
    // const sortDirection = sortingRef.value.length > 0 ? (sortingRef.value[0].desc ? 'desc' : 'asc') : null;
    
    // const params: Record<string, any> = {
    //     page: pagination.value.pageIndex , // Page and per_page are always sent
    //     per_page: pagination.value.pageSize,
    // };

    // if (globalFilter.value) params.search = globalFilter.value;
    // if (selectedStatus.value !== 'All') params.status = selectedStatus.value;
    // if (dateFilterStart.value) params.date_start = dateFilterStart.value;
    // if (dateFilterEnd.value) params.date_end = dateFilterEnd.value;
    // if (sortBy) { 
    //     params.sort_by = sortBy;
    //     params.sort_direction = sortDirection;
    // }

    // form.delete(route('counties.destroy', { id: form.id, ...params }), {
    //     preserveScroll: true,
    //     onSuccess: () => closeModal(),
    //     onError: () => {
    //         isDeleteDialogOpen.value = false;
    //         // alert("Error on delete.");            
    //     },
    //     onFinish: () => { 
    //         form.reset();
    //         isDeleteDialogOpen.value = false;
    //     }
    // });
// };
const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDeleteDialogOpen.value = false;
};

// --- Column Helper For Table ---
const columnHelper = createColumnHelper<SubmissionLog>();

const columns = [
  columnHelper.accessor('id', {
    id: "id",
    header: () => 'ID',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('type', {
    header: 'Actions',
    cell: info => {
      const status = info.getValue() as string;
      let updatedStr = '';
      switch (status) {
        case 'create':
          updatedStr = 'Created';
          break;
        case 'update':
          updatedStr = 'Updated';
          break;
        case 'status_change':
          updatedStr = 'Status Changed';
          break;
        case 'sugg_message':
          updatedStr = 'Feedback sent';
          break;
        case 'sugg_accept':
          updatedStr = 'Suggestion Accepted';
          break;
        case 'sugg_reject':
          updatedStr = 'Suggestoin Rejected';
          break;
        case 'approved':
          updatedStr = 'Approved';
          break;
        case 'rejected':
          updatedStr = 'Rejected';
          break;

        
        default:
          updatedStr = '';
      }
      return updatedStr;
    },
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('status_change.from', {
    header: 'From',
    cell: info => {
      const status = info.getValue() as Submission['status'];
      let colorClass = '';
      switch (status) {
        case 'pending':
          colorClass = 'text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-200';
          break;

        case 'approved':
          colorClass = 'text-green-600 bg-green-100 dark:bg-green-900 dark:text-green-200';
          break;
        case 'rejected':
          colorClass = 'text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-200';
          break;
        case 'reviewing':
          colorClass = 'text-purple-600 bg-purple-100 dark:bg-purple-900 dark:text-purple-200';
          break;
        case 'feedback':
          colorClass = 'text-blue-600 bg-amber-100 dark:bg-amber-900 dark:text-amber-200';
          break;
        default:
          colorClass = '';
      }
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-semibold ${colorClass}` }, status);
    },
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('status_change.to', {
    header: 'To',
    cell: info => {
      const status = info.getValue() as Submission['status'];
      let colorClass = '';
      switch (status) {
        case 'pending':
          colorClass = 'text-blue-600 bg-blue-100 dark:bg-blue-900 dark:text-blue-200';
          break;
        case 'approved':
          colorClass = 'text-green-600 bg-green-100 dark:bg-green-900 dark:text-green-200';
          break;
        case 'rejected':
          colorClass = 'text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-200';
          break;
        case 'reviewing':
          colorClass = 'text-purple-600 bg-purple-100 dark:bg-purple-900 dark:text-purple-200';
          break;
        case 'feedback':
          colorClass = 'text-blue-600 bg-amber-100 dark:bg-amber-900 dark:text-amber-200';
          break;
        default:
          colorClass = '';
      }
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-semibold ${colorClass}` }, status);
    },
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('submission_object.company_name', {
    header: 'Company Name',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('feedback_message', {
    header: 'Feedback',
    cell: info => {
      const value = info.getValue(); // Get the raw value (string, null, or undefined)
      if (value === null || value === undefined) {
        return '-'; // Or an empty string '' if you prefer
      }

      const stringValue = String(value); // Ensure it's treated as a string
      const maxLength = 25; // Define your max length

      if (stringValue.length > maxLength) {
        return stringValue.slice(0, maxLength) + '...'; // Truncate and add ellipsis
      }
      return stringValue; // Return as is if shorter or equal
    },
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('user_object.name', {
    header: 'Feedback By',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('created_at', {
    header: 'Date',
    cell: info => new Date(info.getValue()).toLocaleString(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
]

// --- Table Instance ---
const table = useVueTable<SubmissionLog>({
    get data() { return submissionLogs.value; },
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(), 
    manualPagination: true,
    state: { get sorting() { return sortingRef.value; },
  },
  onSortingChange: updaterOrValue => {
    sortingRef.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(sortingRef.value)
      : updaterOrValue
  },
  onGlobalFilterChange: updaterOrValue => {
    globalFilter.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(globalFilter.value)
      : updaterOrValue
  },
  onPaginationChange: updaterOrValue => (
    pagination.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(pagination.value)
      : updaterOrValue
  ),

});

function navigateToSubmissions(status: string) {

    const params: Record<string, any> = {
        page: 1,
        per_page: 10,
        
    };
    if(status != '') {
        params.status = status;
    }
    console.log(params);
    router.get(route('submissions.index'), params);
}

function handleCellClick(id :string) {
    const params: Record<string, any> = {        
    };

  router.get(route('submissions.edit', { id: id, ...params }));
}

</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- <div class="grid auto-rows-min gap-4 md:grid-cols-3 h-32">
                <div class="h-32 relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="h-32 relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="h-32 relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div> -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-4">
                <div class="bg-background rounded-xl shadow-lg hover:shadow-xl transition-shadow 
                duration-300 overflow-hidden border border-primary-light/25 cursor-pointer
                dark:border-gray-700 flex flex-col justify-center items-center hover:bg-primary/5 py-5 text-blue-500" @click="navigateToSubmissions('pending')">
                    <h2 class="text-5xl font-semibold  mb-2 truncate mt-3" >
                        {{ counts.pending }}
                    </h2>
                    <div class="flex items-center">
                        <MailPlus class="size-5 me-2" />
                        <p class="truncate text-lg" >
                            New Submissions
                        </p>
                    </div>        
                    
                </div>

                <div class="bg-background rounded-xl shadow-lg hover:shadow-xl transition-shadow 
                duration-300 overflow-hidden border border-primary-light/25 cursor-pointer
                dark:border-gray-700 flex flex-col justify-center items-center hover:bg-primary/5 py-5 text-violet-500" @click="navigateToSubmissions('reviewing')">
                    <h2 class="text-5xl font-semibold  mb-2 truncate mt-3">
                        {{ counts.reviewing }} 
                    </h2>
                    <div class="flex items-center  ">
                        <FileSearch2 class="size-5 me-2" />
                        <p class=" truncate text-lg" >
                            In Review
                        </p>
                    </div>        
                    
                </div>

                <div class="bg-background rounded-xl shadow-lg hover:shadow-xl transition-shadow 
                duration-300 overflow-hidden border border-primary-light/25 cursor-pointer
                dark:border-gray-700 flex flex-col justify-center items-center hover:bg-primary/5 py-5 text-amber-500" @click="navigateToSubmissions('feedback')">
                    <h2 class="text-5xl font-semibold  mb-2 truncate mt-3 selection:clear-none">
                        {{ counts.feedback }} 
                    </h2>
                    <div class="flex items-center ">
                        <MessageSquare class="size-5 me-2" />
                        <p class=" truncate text-lg" >
                            In Feedback Loop
                        </p>
                    </div>        
                    
                </div>

                <div class="bg-background rounded-xl shadow-lg hover:shadow-xl transition-shadow 
                duration-300 overflow-hidden border border-primary-light/25 cursor-pointer
                dark:border-gray-700 flex flex-col justify-center items-center hover:bg-primary/5 py-5 text-green-500" @click="navigateToSubmissions('')">
                    <h2 class="text-5xl font-semibold mb-2 truncate mt-3">
                        {{ counts.total }}  
                    </h2>
                    <div class="flex items-center">
                        <PackageCheck class="size-5 me-2 " />
                        <p class=" truncate text-lg " >
                            Total Submissions
                        </p>
                    </div>        
                    
                </div>

               
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <!-- <PlaceholderPattern /> -->
                 <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto ">
                
                
                    <p class="ms-2 font-bold">Lastest Activities</p>
                <!-- Filters Section -->
                <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2"> -->
                    
                    <!-- Keyword Search -->         
                    <!-- <div>
                        <label for="global-search" class="block text-sm font-medium text-gray-700 mb-1">Keyword Search</label>
                        <div class="flex flex-row">
                            <input
                                id="global-search"
                                type="text"
                                v-model="globalFilter"
                                placeholder="Search name, id ..."
                                class="w-full px-4 py-2 border dark:border-gray-800 rounded-md focus:outline-none transition duration-150 ease-in-out"
                            />
                            
                        </div>
                    </div>-->
                <!-- </div>  -->
                
                <!-- Table Container -->
                <div class="overflow-x-auto rounded-lg border dark:border-gray-900 mt-2">
                    <table class="min-w-full divide-y dark:divide-gray-900">
                        <thead class="">
                            <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <th
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    :colSpan="header.colSpan"
                                    :class="{
                                        'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider': true,
                                        'cursor-pointer select-none': header.column.getCanSort(),
                                        'hover:dark:bg-gray-900 hover:bg-gray-100': header.column.getCanSort(),
                                    }"
                                    @click="header.column.getToggleSortingHandler()?.($event)"
                                >
                                <div class="flex items-center space-x-1">
                                    <template v-if="!header.isPlaceholder">
                                        <FlexRender
                                            v-if="!header.isPlaceholder"
                                            :render="header.column.columnDef.header"
                                            :props="header.getContext()"
                                            />
                                        <span v-if="header.column.getIsSorted() === 'asc'">
                                            <svg
                                                class="w-4 h-4 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                            </svg>
                                        </span>
                                        <span v-else-if="header.column.getIsSorted() === 'desc'">
                                            <svg
                                                class="w-4 h-4 text-gray-400"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </span>
                                        <span v-else>
                                            <svg
                                                class="w-4 h-4 text-gray-300"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg"
                                            >
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4"></path>
                                            </svg>
                                        </span>
                                    </template>
                                </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class=" divide-y dark:divide-gray-800">
                            <tr v-if="table.getRowModel().rows.length === 0">
                                <td :colspan="columns.length" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                    No Data found matching your criteria.
                                </td>
                            </tr>
                            <tr v-for="row in table.getRowModel().rows" :key="row.id" 
                                class="cursor-pointer dark:hover:bg-gray-900 hover:bg-gray-100"
                                @click="handleCellClick(row.original.submission_id)">
                                <td
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    class="px-6 py-4 whitespace-nowrap text-sm " 
                                >
                                <Label class="cursor-pointer" >
                                    <!-- Use FlexRender for cell content -->
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </Label>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Controls -->
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-2">
                        <button
                            @click="triggerInertiaVisit({ newPageIndex: 1 })"
                            :disabled="submissionLogsPagination.current_page === 1"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white  dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            First
                        </button>
                        <button
                            @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex - 1 })"
                            :disabled="submissionLogsPagination.current_page === 1"
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex + 1 })"
                            :disabled="submissionLogsPagination.current_page === submissionLogsPagination.last_page"             
                            class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                        <button
                            @click="triggerInertiaVisit({ newPageIndex: submissionLogsPagination.last_page })"
                            :disabled="submissionLogsPagination.current_page === submissionLogsPagination.last_page"
                            class="px-3 py-1 border rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white border-gray-300  dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Last
                        </button>
                    </div>

                    <div class="flex items-center space-x-2 text-sm text-gray-700">
                        <span>Page</span>
                        <strong>
                            {{ submissionLogsPagination.current_page }} of {{ submissionLogsPagination.last_page }}
                        </strong>
                    </div>

                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-700">Go to page:</span>
                        <input
                            type="number"
                            :value=" pagination.pageIndex "                        
                            @change="e => {
                                const newPage = Math.max(1, Math.min(submissionLogsPagination.last_page, Number((e.target as HTMLInputElement).value)));
                                triggerInertiaVisit({ newPageIndex: newPage });
                            }"
                            class="w-20 px-3 py-1 border border-gray-300 dark:border-gray-800 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                        />
                        <select
                            v-model="pagination.pageSize"
                            @change="triggerInertiaVisit({ resetPageIndex: true })"
                            class="px-3 py-1 border border-gray-300 dark:border-gray-800 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                        >
                            <option v-for="size in pageSizes" :key="size" :value="size">Show {{ size }}</option>
                        </select>
                    </div>
                </div>

                <!-- Delete Confirmation Dialog -->
                <Dialog :open="isDeleteDialogOpen" @update:open="isDeleteDialogOpen = $event">
                    <DialogContent>
                        <!-- @submit="handleDelete" -->
                        <form class="space-y-6" >
                            <DialogHeader class="space-y-3">
                                <DialogTitle class=" text-lg/8">Are you sure you want to delete <br />"{{ countryNameToDelete }}"?</DialogTitle>
                                <DialogDescription>
                                    Once after deleted the country, you can't able to do the recovery.
                                </DialogDescription>
                            </DialogHeader>


                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button variant="secondary" @click="closeModal"> Cancel </Button>
                                </DialogClose>

                                <Button type="submit" variant="destructive" :disabled="form.processing"> Delete Request </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
            </div>
        </div>
    </AppLayout>
</template>
