// resources/js/types/DesignationTypes.ts

import { InertiaPaginationLink } from "./submissions";

export type Designation = {
  id: number;
  name: string;  
}

// Interface for the full Inertia paginated response for Feature Requests
export interface DesignationPagination {
  data: Designation[];
  current_page: number;
  last_page: number;
  next_page_url: string | null;
  prev_page_url: string | null;
  links: InertiaPaginationLink[];
  total: number;
  per_page: number;
}


// Type for the date range filter value used in TanStack Table
export interface DateRangeFilterValue {
  start: string | null;
  end: string | null;
}