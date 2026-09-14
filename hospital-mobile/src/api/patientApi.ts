import { authenticatedApiClient } from "./client";
import {
  PatientGender,
  PatientStatus,
  BloodGroupOption,
  PatientListItem,
  PatientRequisitionSummary,
  PatientDetail,
  PatientListQuery,
  PatientPaginationMeta,
  PatientListResponse,
  CreatePatientPayload,
  UpdatePatientPayload,
} from "../types/patient";

function isValidId(id: unknown): id is number {
  return typeof id === "number" && Number.isInteger(id) && id > 0;
}

function isValidGender(gender: unknown): gender is PatientGender {
  return gender === "male" || gender === "female" || gender === "other";
}

function isValidStatus(status: unknown): status is PatientStatus {
  return status === "active" || status === "discharged" || status === "archived";
}

function parseBloodGroupOption(raw: unknown): BloodGroupOption | null {
  if (raw === null || raw === undefined) {
    return null;
  }
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid blood group response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (!isValidId(obj.id) || typeof obj.name !== "string" || obj.name.trim() === "") {
    throw new TypeError("Invalid blood group response shape from server");
  }
  return {
    id: obj.id,
    name: obj.name,
  };
}

function parseRequisitionSummary(raw: unknown): PatientRequisitionSummary {
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid requisition response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (
    !isValidId(obj.id) ||
    typeof obj.blood_group !== "string" ||
    typeof obj.units_needed !== "number" ||
    typeof obj.urgency_level !== "string" ||
    typeof obj.status !== "string"
  ) {
    throw new TypeError("Invalid requisition response shape from server");
  }
  return {
    id: obj.id,
    blood_group: obj.blood_group,
    units_needed: obj.units_needed,
    urgency_level: obj.urgency_level,
    status: obj.status,
    created_at: typeof obj.created_at === "string" ? obj.created_at : null,
  };
}

function parsePatientListItem(raw: unknown): PatientListItem {
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid patient list response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (
    !isValidId(obj.id) ||
    typeof obj.mrn !== "string" ||
    typeof obj.name !== "string" ||
    !isValidGender(obj.gender) ||
    !isValidStatus(obj.status)
  ) {
    throw new TypeError("Invalid patient list response shape from server");
  }
  return {
    id: obj.id,
    mrn: obj.mrn,
    name: obj.name,
    gender: obj.gender,
    status: obj.status,
    ward_name: typeof obj.ward_name === "string" ? obj.ward_name : null,
    room_number: typeof obj.room_number === "string" ? obj.room_number : null,
    bed_number: typeof obj.bed_number === "string" ? obj.bed_number : null,
    blood_group: parseBloodGroupOption(obj.blood_group),
    created_at: typeof obj.created_at === "string" ? obj.created_at : null,
  };
}

function parsePatientDetail(raw: unknown): PatientDetail {
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid patient response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (
    !isValidId(obj.id) ||
    typeof obj.mrn !== "string" ||
    typeof obj.name !== "string" ||
    !isValidGender(obj.gender) ||
    !isValidStatus(obj.status) ||
    typeof obj.date_of_birth !== "string"
  ) {
    throw new TypeError("Invalid patient response shape from server");
  }

  let bloodRequests: PatientRequisitionSummary[] | undefined;
  if (Array.isArray(obj.blood_requests)) {
    bloodRequests = obj.blood_requests.map(parseRequisitionSummary);
  }

  return {
    id: obj.id,
    mrn: obj.mrn,
    name: obj.name,
    gender: obj.gender,
    date_of_birth: obj.date_of_birth,
    contact_number: typeof obj.contact_number === "string" ? obj.contact_number : null,
    status: obj.status,
    ward_name: typeof obj.ward_name === "string" ? obj.ward_name : null,
    room_number: typeof obj.room_number === "string" ? obj.room_number : null,
    bed_number: typeof obj.bed_number === "string" ? obj.bed_number : null,
    blood_group: parseBloodGroupOption(obj.blood_group),
    created_at: typeof obj.created_at === "string" ? obj.created_at : null,
    updated_at: typeof obj.updated_at === "string" ? obj.updated_at : null,
    blood_requests: bloodRequests,
  };
}

function parsePaginationMeta(raw: unknown): PatientPaginationMeta {
  if (typeof raw !== "object" || raw === null) {
    throw new TypeError("Invalid patient list response shape from server");
  }
  const obj = raw as Record<string, unknown>;
  if (
    typeof obj.current_page !== "number" ||
    typeof obj.last_page !== "number" ||
    typeof obj.per_page !== "number" ||
    typeof obj.total !== "number"
  ) {
    throw new TypeError("Invalid patient list response shape from server");
  }
  return {
    current_page: obj.current_page,
    last_page: obj.last_page,
    per_page: obj.per_page,
    total: obj.total,
  };
}

export async function fetchPatients(query?: PatientListQuery): Promise<PatientListResponse> {
  const params: Record<string, string | number> = {};

  if (query) {
    if (query.search !== undefined && query.search !== null) {
      const trimmed = query.search.trim();
      if (trimmed !== "") {
        params.search = trimmed;
      }
    }
    if (query.status && isValidStatus(query.status)) {
      params.status = query.status;
    }
    if (typeof query.page === "number" && Number.isInteger(query.page) && query.page > 0) {
      params.page = query.page;
    }
    if (typeof query.per_page === "number" && Number.isInteger(query.per_page) && query.per_page > 0) {
      params.per_page = query.per_page;
    }
  }

  const response = await authenticatedApiClient.get<unknown>("/patients", { params });

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid patient list response shape from server");
  }

  const resObj = response.data as Record<string, unknown>;
  if (!Array.isArray(resObj.data)) {
    throw new TypeError("Invalid patient list response shape from server");
  }

  const data = resObj.data.map(parsePatientListItem);
  const meta = parsePaginationMeta(resObj.meta);

  return { data, meta };
}

export async function fetchPatientById(patientId: number): Promise<PatientDetail> {
  if (!isValidId(patientId)) {
    throw new TypeError("Invalid patient ID");
  }

  const response = await authenticatedApiClient.get<unknown>(`/patients/${patientId}`);

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid patient response shape from server");
  }

  const resObj = response.data as Record<string, unknown>;
  return parsePatientDetail(resObj.data);
}

export async function createPatient(payload: CreatePatientPayload): Promise<PatientDetail> {
  const requestBody: Record<string, unknown> = {
    name: payload.name,
    mrn: payload.mrn,
    gender: payload.gender,
    date_of_birth: payload.date_of_birth,
  };

  if (payload.blood_group_id !== undefined) {
    requestBody.blood_group_id = payload.blood_group_id;
  }
  if (payload.contact_number !== undefined) {
    requestBody.contact_number = payload.contact_number;
  }
  if (payload.ward_name !== undefined) {
    requestBody.ward_name = payload.ward_name;
  }
  if (payload.room_number !== undefined) {
    requestBody.room_number = payload.room_number;
  }
  if (payload.bed_number !== undefined) {
    requestBody.bed_number = payload.bed_number;
  }

  const response = await authenticatedApiClient.post<unknown>("/patients", requestBody);

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid patient response shape from server");
  }

  const resObj = response.data as Record<string, unknown>;
  return parsePatientDetail(resObj.data);
}

export async function updatePatient(patientId: number, payload: UpdatePatientPayload): Promise<PatientDetail> {
  if (!isValidId(patientId)) {
    throw new TypeError("Invalid patient ID");
  }

  const requestBody: Record<string, unknown> = {};

  if (payload.name !== undefined) {
    requestBody.name = payload.name;
  }
  if (payload.gender !== undefined) {
    requestBody.gender = payload.gender;
  }
  if (payload.date_of_birth !== undefined) {
    requestBody.date_of_birth = payload.date_of_birth;
  }
  if (payload.blood_group_id !== undefined) {
    requestBody.blood_group_id = payload.blood_group_id;
  }
  if (payload.contact_number !== undefined) {
    requestBody.contact_number = payload.contact_number;
  }
  if (payload.ward_name !== undefined) {
    requestBody.ward_name = payload.ward_name;
  }
  if (payload.room_number !== undefined) {
    requestBody.room_number = payload.room_number;
  }
  if (payload.bed_number !== undefined) {
    requestBody.bed_number = payload.bed_number;
  }

  const response = await authenticatedApiClient.put<unknown>(`/patients/${patientId}`, requestBody);

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid patient response shape from server");
  }

  const resObj = response.data as Record<string, unknown>;
  return parsePatientDetail(resObj.data);
}

export async function fetchBloodGroups(): Promise<BloodGroupOption[]> {
  const response = await authenticatedApiClient.get<unknown>("/blood-groups");

  if (typeof response.data !== "object" || response.data === null) {
    throw new TypeError("Invalid blood group response shape from server");
  }

  const resObj = response.data as Record<string, unknown>;
  if (!Array.isArray(resObj.data)) {
    throw new TypeError("Invalid blood group response shape from server");
  }

  return resObj.data.map((item) => {
    const option = parseBloodGroupOption(item);
    if (!option) {
      throw new TypeError("Invalid blood group response shape from server");
    }
    return option;
  });
}
