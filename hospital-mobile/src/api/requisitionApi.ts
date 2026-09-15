import { authenticatedApiClient } from "./client";
import {
  RequisitionStatus,
  RequisitionUrgency,
  RequisitionPatientSummary,
  RequisitionListItem,
  RequisitionDetail,
  RequisitionListQuery,
  RequisitionPaginationMeta,
  RequisitionListResponse,
  CreateRequisitionPayload,
} from "../types/requisition";

function isValidId(id: unknown): id is number {
  return typeof id === "number" && Number.isInteger(id) && id > 0;
}

function isValidStatus(status: unknown): status is RequisitionStatus {
  return (
    status === "pending" ||
    status === "approved" ||
    status === "dispensed" ||
    status === "rejected"
  );
}

function isValidUrgency(urgency: unknown): urgency is RequisitionUrgency {
  return urgency === "routine" || urgency === "urgent" || urgency === "emergency";
}

function parsePatientSummary(raw: unknown): RequisitionPatientSummary | null {
  if (raw === null || raw === undefined) {
    return null;
  }
  if (typeof raw !== "object" || raw === null) {
    return null;
  }
  const obj = raw as Record<string, unknown>;
  if (!isValidId(obj.id) || typeof obj.mrn !== "string" || typeof obj.name !== "string") {
    return null;
  }
  return {
    id: obj.id,
    mrn: obj.mrn,
    name: obj.name,
    gender: typeof obj.gender === "string" ? obj.gender : null,
    date_of_birth: typeof obj.date_of_birth === "string" ? obj.date_of_birth : null,
    contact_number: typeof obj.contact_number === "string" ? obj.contact_number : null,
  };
}

export function parseRequisitionListItem(raw: unknown): RequisitionListItem {
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid requisition list response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (
    !isValidId(obj.id) ||
    typeof obj.blood_group !== "string" ||
    typeof obj.units_needed !== "number" ||
    !isValidUrgency(obj.urgency_level) ||
    !isValidStatus(obj.status)
  ) {
    throw new TypeError("Invalid requisition list response shape from server");
  }

  const patientSummary = parsePatientSummary(obj.patient);
  const patientName = typeof obj.patient_name === "string" ? obj.patient_name : patientSummary?.name || "Unknown Patient";

  return {
    id: obj.id,
    patient_id: isValidId(obj.patient_id) ? obj.patient_id : (patientSummary?.id || null),
    patient_name: patientName,
    blood_group: obj.blood_group,
    units_needed: obj.units_needed,
    urgency_level: obj.urgency_level,
    status: obj.status,
    required_by: typeof obj.required_by === "string" ? obj.required_by : null,
    created_at: typeof obj.created_at === "string" ? obj.created_at : null,
    patient: patientSummary,
  };
}

export function parseRequisitionDetail(raw: unknown): RequisitionDetail {
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid requisition detail response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (
    !isValidId(obj.id) ||
    typeof obj.blood_group !== "string" ||
    typeof obj.units_needed !== "number" ||
    !isValidUrgency(obj.urgency_level) ||
    !isValidStatus(obj.status)
  ) {
    throw new TypeError("Invalid requisition detail response shape from server");
  }

  const patientSummary = parsePatientSummary(obj.patient);
  const patientName = typeof obj.patient_name === "string" ? obj.patient_name : patientSummary?.name || "Unknown Patient";

  return {
    id: obj.id,
    patient_id: isValidId(obj.patient_id) ? obj.patient_id : (patientSummary?.id || null),
    patient_name: patientName,
    blood_group: obj.blood_group,
    units_needed: obj.units_needed,
    urgency_level: obj.urgency_level,
    status: obj.status,
    reason: typeof obj.reason === "string" ? obj.reason : null,
    required_by: typeof obj.required_by === "string" ? obj.required_by : null,
    ward_name: typeof obj.ward_name === "string" ? obj.ward_name : null,
    room_number: typeof obj.room_number === "string" ? obj.room_number : null,
    bed_number: typeof obj.bed_number === "string" ? obj.bed_number : null,
    attendant_name: typeof obj.attendant_name === "string" ? obj.attendant_name : null,
    attendant_phone: typeof obj.attendant_phone === "string" ? obj.attendant_phone : null,
    approved_at: typeof obj.approved_at === "string" ? obj.approved_at : null,
    rejected_at: typeof obj.rejected_at === "string" ? obj.rejected_at : null,
    created_at: typeof obj.created_at === "string" ? obj.created_at : null,
    updated_at: typeof obj.updated_at === "string" ? obj.updated_at : null,
    patient: patientSummary,
  };
}

function parsePaginationMeta(raw: unknown): RequisitionPaginationMeta {
  if (typeof raw !== "object" || raw === null) {
    return { current_page: 1, last_page: 1, per_page: 15, total: 0 };
  }
  const obj = raw as Record<string, unknown>;
  const currentPage = typeof obj.current_page === "number" ? obj.current_page : 1;
  const lastPage = typeof obj.last_page === "number" ? obj.last_page : 1;
  const perPage = typeof obj.per_page === "number" ? obj.per_page : 15;
  const total = typeof obj.total === "number" ? obj.total : 0;
  return { current_page: currentPage, last_page: lastPage, per_page: perPage, total };
}

export async function fetchRequisitions(
  query?: RequisitionListQuery
): Promise<RequisitionListResponse> {
  const params: Record<string, string | number> = {};
  if (query?.status && query.status.trim() !== "") {
    params.status = query.status.trim();
  }
  if (query?.search && query.search.trim() !== "") {
    params.search = query.search.trim();
  }
  if (query?.page && query.page > 0) {
    params.page = query.page;
  }
  if (query?.per_page && query.per_page > 0) {
    params.per_page = query.per_page;
  }

  const response = await authenticatedApiClient.get<unknown>("/requisitions", { params });

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid API response format from server");
  }

  const res = response.data as Record<string, unknown>;
  if (!Array.isArray(res.data)) {
    throw new TypeError("Invalid requisitions list payload from server");
  }

  const items = res.data.map((item) => parseRequisitionListItem(item));
  const meta = parsePaginationMeta(res.meta);

  return { data: items, meta };
}

export async function fetchRequisitionById(id: number): Promise<RequisitionDetail> {
  if (!isValidId(id)) {
    throw new Error("Invalid requisition ID");
  }

  const response = await authenticatedApiClient.get<unknown>(`/requisitions/${id}`);

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid API response format from server");
  }

  const res = response.data as Record<string, unknown>;
  return parseRequisitionDetail(res.data);
}

export async function createRequisition(
  payload: CreateRequisitionPayload
): Promise<RequisitionDetail> {
  const response = await authenticatedApiClient.post<unknown>("/requisitions", payload);

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid API response format from server");
  }

  const res = response.data as Record<string, unknown>;
  return parseRequisitionDetail(res.data);
}
