// resources/js/types/FeatureRequestTypes.ts

// Type for a single Feature Request item
export type FeatureRequest = {
  id: number;
  title: string;
  email: string;
  status: 'pending' | 'approved' | 'rejected' | 'reviewed';
  submitted_at: string;
  note: string,
  description : string 
}

// Interface for the full Inertia paginated response for Feature Requests
export interface FeatureRequestPagination {
  data: FeatureRequest[];
  current_page: number;
  last_page: number;
  next_page_url: string | null;
  prev_page_url: string | null;
  links: InertiaPaginationLink[];
  total: number;
  per_page: number;
}

// Interface for the filters prop sent by Inertia (from Laravel)
export interface InertiaFilters {
  search?: string | null;
  status?: string | null;
  date_start?: string | null;
  date_end?: string | null;  
}

// Interface for the sorting prop sent by Inertia (from Laravel)
export interface InertiaSorting {
  sort_by?: string | null;
  sort_direction?: 'asc' | 'desc' | null;
}








// Interface for a single Inertia pagination link
export interface InertiaPaginationLink {
  url: string | null;
  label: string;
  active: boolean;
}



// Type for the date range filter value used in TanStack Table
export interface DateRangeFilterValue {
  start: string | null;
  end: string | null;
}