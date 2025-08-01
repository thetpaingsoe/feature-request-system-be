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
    Designation, 
    DesignationPagination, 
} from '@/types/designation';

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
import { InertiaFilters, InertiaSorting } from '@/types/submissions';

// --- Breadcumb ---
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Designations',
        href: '/designations',
    }
];

// --- Pagination Controls ---
const pageSizes: number[] = [10, 20, 50, 100];

// --- Handling Props ---
const props = defineProps<{
  companyDesignationsPagination: DesignationPagination; 
  filters: InertiaFilters; 
  sorting: InertiaSorting; 
}>();

const companyDesignationsPagination = computed(() => props.companyDesignationsPagination ?? []);
const companyDesignations = ref<Designation[]>(props.companyDesignationsPagination ? props.companyDesignationsPagination.data : []); // Use the 'data' array from the pagination prop

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
    pageIndex: companyDesignationsPagination.value.current_page - 1, // Initialize from props (0-indexed)
    pageSize: companyDesignationsPagination.value.per_page, // Initialize from props
});

// --- Update table data when there is changes ---
watch(() => props.companyDesignationsPagination, (newCompanyDesignationsData) => {        
  companyDesignations.value = newCompanyDesignationsData ? newCompanyDesignationsData.data : [];
  pagination.value.pageIndex = newCompanyDesignationsData ? newCompanyDesignationsData.current_page : 1;
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

    router.get(route('designations.index'), params,
    {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        only: ['companyDesignationsPagination', 'filters', 'sorting', 'flash'],
    }
)};

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

const isDeleteDialogOpen = ref(false);
const designationNameToDelete = ref<string>('');

const form = useForm({
    id: 0,
});

const closeModal = () => {
    form.clearErrors();
    form.reset();
    isDeleteDialogOpen.value = false;
};

// --- Column Helper For Table ---
const columnHelper = createColumnHelper<Designation>();

const columns = [
  columnHelper.accessor('id', {
    id: "id",
    header: () => 'ID',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.accessor('name', {
    header: 'Name',
    cell: info => info.getValue(),
    enableSorting: true,
    enableGlobalFilter: true,
  }),
  columnHelper.display({ 
    id: 'actions',
    header: 'Actions',
    cell: ({  }) => {//row
    //   const item = row.original; // Get the original data object for the row
      return h('div', { class: 'flex space-x-2' }, [
        h('button', {
          class: 'cursor-not-allowed  px-3 py-1 bg-blue-500 dark:bg-blue-800 text-white rounded-md hover:bg-blue-600 dark:hover:bg-blue-900 transition duration-150 ease-in-out text-sm',
          onClick: () => {} // handleEdit(item.id),
        }, 'Edit'),
        h('button', {
          class: ' cursor-not-allowed px-3 py-1 bg-red-500 dark:bg-red-800 text-white rounded-md hover:bg-red-600 dark:hover:bg-red-900 transition duration-150 ease-in-out text-sm',
          onClick: () => {
            
          },
        }, 'Delete'),
      ]);
    },
    enableSorting: false,
  }),
]

// --- Table Instance ---
const table = useVueTable<Designation>({
    get data() { return companyDesignations.value; },
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

</script>

<template>
    <Head title="Designations" />
    
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
                            placeholder="Search name, id ..."
                            class="w-full px-4 py-2 border dark:border-gray-800 rounded-md focus:outline-none transition duration-150 ease-in-out"
                        />
                        
                    </div>
                </div>
            </div>
            
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
                        :disabled="companyDesignationsPagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white  dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        First
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex - 1 })"
                        :disabled="companyDesignationsPagination.current_page === 1"
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: pagination.pageIndex + 1 })"
                        :disabled="companyDesignationsPagination.current_page === companyDesignationsPagination.last_page"             
                        class="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                    <button
                        @click="triggerInertiaVisit({ newPageIndex: companyDesignationsPagination.last_page })"
                        :disabled="companyDesignationsPagination.current_page === companyDesignationsPagination.last_page"
                        class="px-3 py-1 border rounded-md text-sm font-medium text-gray-700 dark:text-white bg-white border-gray-300  dark:bg-gray-700 dark:border-gray-800 hover:bg-gray-50 hover:dark:bg-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Last
                    </button>
                </div>

                <div class="flex items-center space-x-2 text-sm text-gray-700">
                    <span>Page</span>
                    <strong>
                        {{ companyDesignationsPagination.current_page }} of {{ companyDesignationsPagination.last_page }}
                    </strong>
                </div>

                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-700">Go to page:</span>
                    <input
                        type="number"
                        :value=" pagination.pageIndex "                        
                        @change="e => {
                            const newPage = Math.max(1, Math.min(companyDesignationsPagination.last_page, Number((e.target as HTMLInputElement).value)));
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
                            <DialogTitle class=" text-lg/8">Are you sure you want to delete <br />"{{ designationNameToDelete }}"?</DialogTitle>
                            <DialogDescription>
                                Once after deleted the designation, you can't able to do the recovery.
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