// resources/js/types/SubmissionTypes.ts

type ShareValue = {
  id: string;
  currency: string;
  amount: number;
};

type Shareholder = {
  name: string;
  email: string;
  percentage: number;
};

type BeneficialOwner = {
  name: string;
  relationship: string;
};

type Director = {
  name: string;
  position: string;
  email: string;
};

// Type for a single Submission item
export type Submission = {
  id: number;
  user_id: number;
  full_name: string;
  email: string;
  company_name: string;
  alternative_company_name: string | null;
  company_designation_id: number | null;
  company_designation?: {
    id: number;
    name: string;
  };

  jurisdiction_of_operation_id: number | null;
  jurisdiction_of_operation?: {
    id: number;
    name: string;
    code: string;
  };

  target_jurisdictions: number[];
  target_jurisdiction_names?: {
    id: number;
    name: string;
    code: string;
  }[]; // from accessor

  number_of_shares: ShareValue | null;
  are_all_shares_issued: boolean;
  number_of_issued_shares: number | null;

  share_value_id: number | null;
  share_value?: {
    id: number;
    currency: string;
    amount: number;
    formatted_value: string;
  };

  shareholders: Shareholder[]; 
  beneficial_owners: BeneficialOwner[];
  directors: Director[];

  status: 'pending' | 'approved' | 'rejected' | 'reviewed';

  note?: string;
  description?: string;

  submitted_at: string;
  created_at: string;
  updated_at: string;
};

// Interface for the full Inertia paginated response for Submissions
export interface SubmissionPagination {
  data: Submission[];
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