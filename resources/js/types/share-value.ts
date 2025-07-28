// resources/js/types/ShareValueTypes.ts

import { InertiaPaginationLink } from "./submissions";

// Type for a single Feature Request item
export type ShareValue = {
  id: number;
  currency: string;
  amount : number;
}

// Interface for the full Inertia paginated response for Feature Requests
export interface ShareValuePagination {
  data: ShareValue[];
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