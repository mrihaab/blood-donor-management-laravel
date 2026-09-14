export type PatientGender = "male" | "female" | "other";
export type PatientStatus = "active" | "discharged" | "archived";

export interface BloodGroupOption {
  id: number;
  name: string;
}

export interface PatientListItem {
  id: number;
  mrn: string;
  name: string;
  gender: PatientGender;
  status: PatientStatus;
  ward_name: string | null;
  room_number: string | null;
  bed_number: string | null;
  blood_group: BloodGroupOption | null;
  created_at: string | null;
}

export interface PatientRequisitionSummary {
  id: number;
  blood_group: string;
  units_needed: number;
  urgency_level: string;
  status: string;
  created_at: string | null;
}

export interface PatientDetail {
  id: number;
  mrn: string;
  name: string;
  gender: PatientGender;
  date_of_birth: string;
  contact_number: string | null;
  status: PatientStatus;
  ward_name: string | null;
  room_number: string | null;
  bed_number: string | null;
  blood_group: BloodGroupOption | null;
  created_at: string | null;
  updated_at: string | null;
  blood_requests?: PatientRequisitionSummary[];
}

export interface PatientListQuery {
  search?: string;
  status?: PatientStatus;
  page?: number;
  per_page?: number;
}

export interface PatientPaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface PatientListResponse {
  data: PatientListItem[];
  meta: PatientPaginationMeta;
}

export interface PatientDetailResponse {
  data: PatientDetail;
}

export interface BloodGroupListResponse {
  data: BloodGroupOption[];
}

export interface CreatePatientPayload {
  name: string;
  mrn: string;
  gender: PatientGender;
  date_of_birth: string;
  blood_group_id?: number | null;
  contact_number?: string | null;
  ward_name?: string | null;
  room_number?: string | null;
  bed_number?: string | null;
}

export interface UpdatePatientPayload {
  name?: string;
  gender?: PatientGender;
  date_of_birth?: string;
  blood_group_id?: number | null;
  contact_number?: string | null;
  ward_name?: string | null;
  room_number?: string | null;
  bed_number?: string | null;
}

export interface ApiValidationErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
}
