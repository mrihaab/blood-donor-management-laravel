export type RequisitionStatus = "pending" | "approved" | "dispensed" | "rejected";

export type RequisitionUrgency = "routine" | "urgent" | "emergency";

export interface RequisitionPatientSummary {
  id: number;
  mrn: string;
  name: string;
  gender?: string | null;
  date_of_birth?: string | null;
  contact_number?: string | null;
}

export interface RequisitionListItem {
  id: number;
  patient_id: number | null;
  patient_name: string;
  blood_group: string;
  units_needed: number;
  urgency_level: RequisitionUrgency;
  status: RequisitionStatus;
  required_by: string | null;
  created_at: string | null;
  patient?: RequisitionPatientSummary | null;
}

export interface RequisitionDetail {
  id: number;
  patient_id: number | null;
  patient_name: string;
  blood_group: string;
  units_needed: number;
  urgency_level: RequisitionUrgency;
  status: RequisitionStatus;
  reason: string | null;
  required_by: string | null;
  ward_name?: string | null;
  room_number?: string | null;
  bed_number?: string | null;
  attendant_name?: string | null;
  attendant_phone?: string | null;
  approved_at: string | null;
  rejected_at: string | null;
  created_at: string | null;
  updated_at: string | null;
  patient?: RequisitionPatientSummary | null;
}

export interface RequisitionListQuery {
  status?: string;
  search?: string;
  page?: number;
  per_page?: number;
}

export interface RequisitionPaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface RequisitionListResponse {
  data: RequisitionListItem[];
  meta: RequisitionPaginationMeta;
}

export interface RequisitionDetailResponse {
  data: RequisitionDetail;
}

export interface CreateRequisitionPayload {
  patient_id: number;
  blood_group: string;
  units_needed: number;
  urgency_level: RequisitionUrgency;
  required_by?: string | null;
  reason?: string | null;
  attendant_name?: string | null;
  attendant_phone?: string | null;
  ward_name?: string | null;
  room_number?: string | null;
  bed_number?: string | null;
}
