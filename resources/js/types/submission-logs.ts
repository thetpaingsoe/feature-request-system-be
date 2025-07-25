// resources/js/types/SubmissionTypes.ts

import { InertiaPaginationLink } from "./feature-request";

type UserType = {
  id: string;
  name: string;
  email: string;
};

type StatusChangeType = {
  from: string;
  to: string;
};


// Type for a single Submission item
export type SubmissionLog = {
  id: number;
  type: string;
  data: string;
  user_object: UserType; 
  created_at: string;
  updated_at: string;
  status_change : StatusChangeType,
  feedback_message : string
};

// Interface for the full Inertia paginated response for Submissions
export interface SubmissionLogPagination {
  data: SubmissionLog[];
  current_page: number;
  last_page: number;
  next_page_url: string | null;
  prev_page_url: string | null;
  links: InertiaPaginationLink[];
  total: number;
  per_page: number;
}
