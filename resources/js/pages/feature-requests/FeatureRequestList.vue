<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, usePage} from '@inertiajs/vue3';
import { ref, computed, h, watch } from 'vue';
import Label from '@/components/ui/label/Label.vue';
import { useDebounceFn } from '@vueuse/core';
import { formatDate } from '@/lib/date-utils';

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
  featureRequestsPagnication: FeatureRequestPagination; 
  filters: InertiaFilters; 
  sorting: InertiaSorting; 
}>();

const featureRequestsPagnication = computed(() => props.featureRequestsPagnication );
const featureRequests = ref<FeatureRequest[]>(props.featureRequestsPagnication.data); // Use the 'data' array from the pagination prop

// --- Table State ---
const filterTypeData = props.filters ?? ref<any[]>([]);
const sorting = ref<any[]>([]);
const globalFilter = ref<string>(filterTypeData.search || '');
const selectedStatus = ref<string>('All');
const dateFilterStart = ref<string>('');
const dateFilterEnd = ref<string>('');

const pagination = ref({
    pageIndex: featureRequestsPagnication.value.current_page - 1, // Initialize from props (0-indexed)
    pageSize: featureRequestsPagnication.value.per_page, // Initialize from props
});

// --- Update table data when there is changes ---
watch(() => featureRequestsPagnication.value, (newFeatureRequestsData) => {    
  featureRequests.value = newFeatureRequestsData.data;
  pagination.value.pageIndex = newFeatureRequestsData.current_page;
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
    
    router.get(route('feature-requests.index'),
    {
        search: globalFilter.value,
        status: selectedStatus.value === 'All' ? null : selectedStatus.value,
        date_start: dateFilterStart.value || null,
        date_end: dateFilterEnd.value || null,
        page : pagination.value.pageIndex,
        per_page: pagination.value.pageSize,
        sort_by: sortBy,         
        sort_direction: sortDirection, 
    },
    {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['featureRequestsPagnication', 'filters', 'sorting'],
    }
)};


// --- Keyword Search ---
watch(
    globalFilter,
    useDebounceFn((_) => {
        triggerInertiaVisit({ resetPageIndex: true })    
    }, 500)
)

// --- Sorting ---
watch(sorting, (newSorting) => {
    console.log("sorting");
    console.log(newSorting);
    triggerInertiaVisit({ resetPageIndex: true });  
}, { deep: true }); 

// @todo --- Status ----
const statusFilterFn: FilterFn<FeatureRequest> = (row, columnId, filterValue: string) => {
  if (!filterValue || filterValue === 'All') {
    return true;
  }
  return row.getValue(columnId) === filterValue;
};
watch(selectedStatus, (newValue: string) => {
  table.getColumn('status')?.setFilterValue(newValue);
});

// @todo --- Date Range ----
watch([dateFilterStart, dateFilterEnd], ([newStart, newEnd]) => {
    //   table.getColumn('submitted_at')?.setFilterValue({ start: newStart, end: newEnd } as DateRangeFilterValue);
});

// @todo --- Action Handlers ---
const handleEdit = (id: number): void => {
  alert(`Editing feature request with ID: ${id}`);
};
const handleDelete = (id: number): void => {
  if (confirm(`Are you sure you want to delete feature request with ID: ${id}?`)) {
    alert(`Feature request with ID: ${id} deleted.`);
  }
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
          colorClass = 'text-blue-600 bg-blue-100';
          break;
        case 'approved':
          colorClass = 'text-green-600 bg-green-100';
          break;
        case 'rejected':
          colorClass = 'text-red-600 bg-red-100';
          break;
        case 'reviewed':
          colorClass = 'text-purple-600 bg-purple-100';
          break;
        default:
          colorClass = 'text-gray-600 bg-gray-100';
      }
      return h('span', { class: `px-2 py-1 rounded-full text-xs font-semibold ${colorClass}` }, status);
    },
    enableSorting: true,
    filterFn: statusFilterFn, // Use custom status filter
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
          class: 'px-3 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition duration-150 ease-in-out text-sm',
          onClick: () => handleEdit(item.id),
        }, 'Edit'),
        h('button', {
          class: 'px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition duration-150 ease-in-out text-sm',
          onClick: () => handleDelete(item.id),
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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
        
            <!-- Filters Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                
                <!-- Keyword Search -->         
                <div>
                    <label for="global-search" class="block text-sm font-medium text-gray-700 mb-1">Keyword Search</label>
                    <input
                        id="global-search"
                        type="text"
                        v-model="globalFilter"
                        placeholder="Search title, email, ID..."
                        class="w-full px-4 py-2 border dark:border-gray-800 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                    />
                </div>

                <!-- Status Filter -->
                <!-- <div>
                <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Status</label>
                <select
                    id="status-filter"
                    v-model="selectedStatus"
                    class="w-full px-4 py-2 border border-gray-800 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                >
                    <option value="All">All Statuses</option>
                    <option value="Pending">pending</option>
                    <option value="Approved">approved</option>
                    <option value="Rejected">rejected</option>
                    <option value="In Progress">reviewed</option>
                </select>
                </div> -->

                <!-- Date Range Filter -->
                <!-- <div class="flex flex-col sm:flex-row gap-2">
                    <div class="flex-1">
                        <label for="date-start" class="block text-sm font-medium text-gray-700 mb-1">Date Start</label>
                        <input
                        id="date-start"
                        type="date"
                        v-model="dateFilterStart"
                        class="w-full px-4 py-2 border border-gray-800 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                        />
                    </div>
                    <div class="flex-1">
                        <label for="date-end" class="block text-sm font-medium text-gray-700 mb-1">Date End</label>
                        <input
                        id="date-end"
                        type="date"
                        v-model="dateFilterEnd"
                        class="w-full px-4 py-2 border border-gray-800 rounded-md focus:ring-blue-500 focus:border-blue-500 transition duration-150 ease-in-out"
                        />
                    </div>
                </div> -->
            </div>

            <!-- Table Container -->
            <div class="overflow-x-auto rounded-lg border dark:border-gray-900">
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
                        :disabled="featureRequestsPagnication.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        First
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex - 1 })"
                        :disabled="featureRequestsPagnication.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex + 1 })"
                        :disabled="featureRequestsPagnication.current_page === featureRequestsPagnication.last_page"             
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: featureRequestsPagnication.last_page })"
                        :disabled="featureRequestsPagnication.current_page === featureRequestsPagnication.last_page"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Last
                    </button>
                </div>

                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <span>Page</span>
                    <strong>
                        {{ featureRequestsPagnication.current_page }} of {{ featureRequestsPagnication.last_page }}
                    </strong>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700">Go to page:</span>
                    <input
                        type="number"
                        :value=" pagination.pageIndex "                        
                        @change="e => {
                            const newPage = Math.max(1, Math.min(featureRequestsPagnication.last_page, Number((e.target as HTMLInputElement).value)));
                            triggerInertiaVisit({ newPageIndex: newPage });
                        }"
                        class="w-20 px-3 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                    />
                    <select
                        v-model="pagination.pageSize"
                        @change="triggerInertiaVisit({ resetPageIndex: true })"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option v-for="size in pageSizes" :key="size" :value="size">Show {{ size }}</option>
                    </select>
                </div>
            </div>

        </div>
    </AppLayout>
</template>
