<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage} from '@inertiajs/vue3';
import { ref, computed, h, watch, onMounted } from 'vue';
import Label from '@/components/ui/label/Label.vue';
import { useDebounceFn } from '@vueuse/core';
import { formatDate } from '@/lib/date-utils';
import { FilterIcon } from 'lucide-vue-next';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import {
    useVueTable,
    getCoreRowModel,
    getFilteredRowModel,
    FlexRender,
    FilterFn,
    createColumnHelper,
} from '@tanstack/vue-table';

import { 
    FeatureRequest, 
    FeatureRequestPagination, 
    InertiaFilters, InertiaSorting 
} from '@/types/feature-request';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';



// --- Breadcumb ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feature Requests',
        href: '/feature-requests',
    }
];

// --- Pagination Controls ---
const pageSizes: number[] = [10, 20, 50, 100];

// --- Handling Props ---
const props = defineProps<{
  featureRequestsPagination: FeatureRequestPagination; 
  filters: InertiaFilters; 
  sorting: InertiaSorting; 
}>();

const featureRequestsPagination = computed(() => props.featureRequestsPagination ?? []);
const featureRequests = ref<FeatureRequest[]>(props.featureRequestsPagination ? props.featureRequestsPagination.data : []); // Use the 'data' array from the pagination prop

// --- Table State ---
const filterTypeData = props.filters ?? ref<any[]>([]);
const sorting = ref<any[]>([]);
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
    pageIndex: featureRequestsPagination.value.current_page - 1, // Initialize from props (0-indexed)
    pageSize: featureRequestsPagination.value.per_page, // Initialize from props
});

// --- Update table data when there is changes ---
watch(() => props.featureRequestsPagination, (newFeatureRequestsData) => {        
  featureRequests.value = newFeatureRequestsData ? newFeatureRequestsData.data : [];
  pagination.value.pageIndex = newFeatureRequestsData ? newFeatureRequestsData.current_page : 1;
}, { deep: true, immediate: true }); 
 
// --- Intertia Request ---
const triggerInertiaVisit = (options?: { resetPageIndex?: boolean; newPageIndex?: number }) => {
    if (options?.resetPageIndex) {
        pagination.value.pageIndex = 1; 
    } else if (options?.newPageIndex !== undefined) {
        pagination.value.pageIndex = options.newPageIndex; // Set to a specific new page index
    }

    const sortBy = sorting.value.length > 0 ? sorting.value[0].id : null;
    const sortDirection = sorting.value.length > 0 ? (sorting.value[0].desc ? 'desc' : 'asc') : null;
    
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

    router.get(route('feature-requests.index'), params,
    {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['featureRequestsPagination', 'filters', 'sorting'],
    }
)};

// -- Filter Sections --
const filterSection = ref(false);
function toggleFilterSection() {
    filterSection.value = !filterSection.value;
}

// --- Keyword Search ---
watch(
    globalFilter,
    useDebounceFn((_) => {
        triggerInertiaVisit({ resetPageIndex: true })    
    }, 500)
)

// --- Sorting ---
watch(sorting, (newSorting) => {
    triggerInertiaVisit({ resetPageIndex: true });
}, { deep: true }); 

// --- Status ----
watch(selectedStatus, (_: string) => {
    triggerInertiaVisit({ resetPageIndex: true });
});

// --- Date Range ----
watch(dateFilter, (_) => {
    if (dateFilter != null && dateFilter.value.length > 1) {
        const tempStartDate = new Date(dateFilter.value[0]); 
        tempStartDate.setHours(0, 0, 0, 0); 
        dateFilterStart.value = tempStartDate.toISOString();

        const tempEndDate = new Date(dateFilter.value[1]);
        tempEndDate.setHours(23, 59, 59, 999); 
        dateFilterEnd.value = tempEndDate.toISOString();        

        triggerInertiaVisit({ resetPageIndex: true });
    }
});
function dateFilterCleared() {
    dateFilter.value = [];
    dateFilterStart.value = "";
    dateFilterEnd.value = "";
    triggerInertiaVisit({ resetPageIndex: true });
}

// @todo --- Action Handlers ---
const handleEdit = (id: number): void => {
    console.log("Edit")
    console.log(id)
  router.get(route('feature-requests.edit', { id: id }));
};

const isDeleteDialogOpen = ref(false);
const featureRequestNameToDelete = ref<string>('');

const form = useForm({
    id: 0,
});
const handleDelete = (e: Event) => {
    e.preventDefault();

    const sortBy = sorting.value.length > 0 ? sorting.value[0].id : null;
    const sortDirection = sorting.value.length > 0 ? (sorting.value[0].desc ? 'desc' : 'asc') : null;
    
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

    form.delete(route('feature-requests.destroy', { id: form.id, ...params }), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => {
            isDeleteDialogOpen.value = false;
            alert("Error on delete.");            
        },
        onFinish: () => { 
            form.reset();
            isDeleteDialogOpen.value = false;
        }
    });
};
const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDeleteDialogOpen.value = false;
};

// --- Column Helper For Table ---
const columnHelper = createColumnHelper<FeatureRequest>();

const columns = [
  columnHelper.accessor('id', {
    id: "id",
    header: () => 'ID',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('title', {
    header: 'Title',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('email', {
    header: 'Email',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    cell: info => {
      const status = info.getValue() as FeatureRequest['status'];
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
        case 'reviewed':
          colorClass = 'text-purple-600 bg-purple-100 dark:bg-purple-900 dark:text-purple-200';
          break;
        default:
          colorClass = 'text-gray-600 bg-gray-100 dark:bg-gary-900 dark:text-gray-200';
      }
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-semibold ${colorClass}` }, status);
    },
    enableSorting: true,
  }),
  columnHelper.accessor('submitted_at', {
    header: 'Submitted At',
    cell: info => formatDate(info.getValue() as string),
    enableSorting: true,
    // filterFn: dateRangeFilterFn, 
  }),
  columnHelper.display({ 
    id: 'actions',
    header: 'Actions',
    cell: ({ row }) => {
      const item = row.original; // Get the original data object for the row
      return h('div', { class: 'flex space-x-2' }, [
        h('button', {
          class: 'px-3 py-1 bg-blue-500 dark:bg-blue-800 text-white rounded-md hover:bg-blue-600 dark:hover:bg-blue-900 transition duration-150 ease-in-out text-sm',
          onClick: () => handleEdit(item.id),
        }, 'Edit'),
        h('button', {
          class: 'px-3 py-1 bg-red-500 dark:bg-red-800 text-white rounded-md hover:bg-red-600 dark:hover:bg-red-900 transition duration-150 ease-in-out text-sm',
          onClick: () => {
            
            featureRequestNameToDelete.value = item.title; 
            form.id = item.id
            
            isDeleteDialogOpen.value = true; 
          },
        }, 'Delete'),
      ]);
    },
    enableSorting: false,
  }),
]

// --- Table Instance ---
const table = useVueTable<FeatureRequest>({
    get data() { return featureRequests.value; },
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
    getFilteredRowModel: getFilteredRowModel(), 
    manualPagination: true,
    state: { get sorting() { return sorting.value; },
  },
  onSortingChange: updaterOrValue => {
    sorting.value = typeof updaterOrValue === 'function'
      ? updaterOrValue(sorting.value)
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

</script>

<template>
    <Head title="Feature Requests" />
    
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto ">
        
            <!-- Filters Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-2">
                
                <!-- Keyword Search -->         
                <div>
                    <label for="global-search" class="block text-sm font-medium text-gray-700 mb-1">Keyword Search</label>
                    <div class="flex flex-row">
                        <input
                            id="global-search"
                            type="text"
                            v-model="globalFilter"
                            placeholder="Search title, email, ID..."
                            class="w-full px-4 py-2 border dark:border-gray-800 rounded-md focus:outline-none transition duration-150 ease-in-out"
                        />
                        <button
                            @click="toggleFilterSection()"
                            class=" ms-2 px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-400 dark:text-white bg-white  dark:bg-gray-800 dark:border-gray-900 hover:bg-gray-50 hover:dark:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <component :is="FilterIcon" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
            
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-[500px]"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 max-h-[500px]"
                leave-to-class="opacity-0 max-h-0"
            >
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-2 border border-dotted px-3 py-2 rounded-lg" v-if="filterSection">
                    <!-- Status Filter -->
                    <div>
                        <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                        <select
                            id="status-filter"
                            v-model="selectedStatus"
                            class="w-full px-4 py-2 dark:text-gray-200 border border-gray-200 dark:border-gray-800 rounded-md focus:ring-gray-500 focus:border-gray-500 transition duration-150 ease-in-out focus:outline-none"
                        >
                            <option value="All">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                            <option value="reviewed">reviewed</option>
                        </select>
                    </div>

                    <!-- Date Range Filter -->
                    <div class="flex flex-col sm:flex-row gap-2">
                        <div class="flex-1">
                            <label for="date-range" class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
                            <VueDatePicker for="date-range" v-model="dateFilter" range :dark="isDark" :enable-time-picker="false" @cleared="dateFilterCleared" :clearable="true"/>
                        </div>                        
                    </div>
                
                </div>
            </Transition>
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
                                No feature requests found matching your criteria.
                            </td>
                        </tr>
                        <tr v-for="row in table.getRowModel().rows" :key="row.id">
                            <td
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                                class="px-6 py-4 whitespace-nowrap text-sm "
                            >
                            <Label >
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
                        :disabled="featureRequestsPagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white  dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        First
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex - 1 })"
                        :disabled="featureRequestsPagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex + 1 })"
                        :disabled="featureRequestsPagination.current_page === featureRequestsPagination.last_page"             
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: featureRequestsPagination.last_page })"
                        :disabled="featureRequestsPagination.current_page === featureRequestsPagination.last_page"
                        class="px-3 py-1 border rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white border-gray-300  dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Last
                    </button>
                </div>

                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <span>Page</span>
                    <strong>
                        {{ featureRequestsPagination.current_page }} of {{ featureRequestsPagination.last_page }}
                    </strong>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700">Go to page:</span>
                    <input
                        type="number"
                        :value=" pagination.pageIndex "                        
                        @change="e => {
                            const newPage = Math.max(1, Math.min(featureRequestsPagination.last_page, Number((e.target as HTMLInputElement).value)));
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
                    <form class="space-y-6" @submit="handleDelete">
                        <DialogHeader class="space-y-3">
                            <DialogTitle class=" text-lg/8">Are you sure you want to delete <br />"{{ featureRequestNameToDelete }}"?</DialogTitle>
                            <DialogDescription>
                                Once your account is deleted, all of its resources and data will also be permanently deleted. 
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
    </AppLayout>
</template>

<style>
.dp__theme_dark {
    --dp-background-color: hsl(0 0% 3.9%);
    --dp-border-color: #1e2939;
    --dp-border-color-hover: #1e2939;
    --dp-border-color-focus: #1e2939;
}
</style>