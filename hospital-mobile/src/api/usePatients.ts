import { useQuery, useMutation, useQueryClient, UseQueryResult, UseMutationResult } from "@tanstack/react-query";
import {
  fetchPatients,
  fetchPatientById,
  createPatient,
  updatePatient,
  fetchBloodGroups,
} from "./patientApi";
import {
  PatientListQuery,
  PatientListResponse,
  PatientDetail,
  BloodGroupOption,
  CreatePatientPayload,
  UpdatePatientPayload,
  PatientStatus,
} from "../types/patient";
import { useAuth } from "../auth/AuthContext";

export interface NormalizedPatientListQuery {
  page: number;
  per_page: number;
  search?: string;
  status?: PatientStatus;
}

export function isPositiveIntegerId(id: unknown): id is number {
  return typeof id === "number" && Number.isInteger(id) && id > 0;
}

function isValidStatus(status: unknown): status is PatientStatus {
  return status === "active" || status === "discharged" || status === "archived";
}

export function normalizePatientListQuery(query?: PatientListQuery): NormalizedPatientListQuery {
  const page = typeof query?.page === "number" && Number.isInteger(query.page) && query.page > 0
    ? query.page
    : 1;
  const per_page = typeof query?.per_page === "number" && Number.isInteger(query.per_page) && query.per_page > 0
    ? query.per_page
    : 15;

  const normalized: NormalizedPatientListQuery = { page, per_page };

  if (query?.search !== undefined && query.search !== null) {
    const trimmed = query.search.trim();
    if (trimmed !== "") {
      normalized.search = trimmed;
    }
  }

  if (query?.status && isValidStatus(query.status)) {
    normalized.status = query.status;
  }

  return normalized;
}

export const patientKeys = {
  root: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "patients"] as const,
  lists: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "patients", "list"] as const,
  list: (hospitalId: number | null | undefined, query?: NormalizedPatientListQuery) =>
    ["hospital", hospitalId, "patients", "list", query] as const,
  details: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "patients", "detail"] as const,
  detail: (hospitalId: number | null | undefined, patientId: number | null | undefined) =>
    ["hospital", hospitalId, "patients", "detail", patientId] as const,
  bloodGroups: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "blood-groups"] as const,
};

export const patientRetryPolicy = (failureCount: number, error: unknown): boolean => {
  if (failureCount >= 1) {
    return false;
  }
  if (error instanceof TypeError || error instanceof SyntaxError) {
    return false;
  }
  if (error && typeof error === "object" && "isAxiosError" in error) {
    const axiosError = error as { response?: { status?: number } };
    const status = axiosError.response?.status;
    if (status && [401, 403, 404, 422, 429].includes(status)) {
      return false;
    }
  }
  return true;
};

export function usePatients(
  hospitalIdOverride?: number | null,
  query?: PatientListQuery
): UseQueryResult<PatientListResponse, Error> {
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;
  const enabled = isPositiveIntegerId(hospitalId);
  const normalizedQuery = normalizePatientListQuery(query);

  return useQuery<PatientListResponse, Error>({
    queryKey: patientKeys.list(hospitalId, normalizedQuery),
    queryFn: () => fetchPatients(normalizedQuery),
    enabled,
    retry: patientRetryPolicy,
  });
}

export function usePatient(
  patientId: number | null | undefined,
  hospitalIdOverride?: number | null
): UseQueryResult<PatientDetail, Error> {
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;
  const enabled = isPositiveIntegerId(hospitalId) && isPositiveIntegerId(patientId);

  return useQuery<PatientDetail, Error>({
    queryKey: patientKeys.detail(hospitalId, patientId),
    queryFn: () => fetchPatientById(patientId!),
    enabled,
    retry: patientRetryPolicy,
  });
}

export function useBloodGroups(
  hospitalIdOverride?: number | null
): UseQueryResult<BloodGroupOption[], Error> {
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;
  const enabled = isPositiveIntegerId(hospitalId);

  return useQuery<BloodGroupOption[], Error>({
    queryKey: patientKeys.bloodGroups(hospitalId),
    queryFn: fetchBloodGroups,
    enabled,
    staleTime: 1000 * 60 * 60 * 24, // 24 hours
    retry: patientRetryPolicy,
  });
}

export function useCreatePatient(
  hospitalIdOverride?: number | null
): UseMutationResult<PatientDetail, Error, CreatePatientPayload> {
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;

  return useMutation<PatientDetail, Error, CreatePatientPayload>({
    mutationFn: (payload: CreatePatientPayload) => createPatient(payload),
    onSuccess: (newPatient: PatientDetail) => {
      if (isPositiveIntegerId(hospitalId)) {
        queryClient.invalidateQueries({ queryKey: patientKeys.lists(hospitalId) });
        queryClient.invalidateQueries({ queryKey: ["hospital", "dashboard", hospitalId] });
        queryClient.setQueryData(patientKeys.detail(hospitalId, newPatient.id), newPatient);
      }
    },
  });
}

export function useUpdatePatient(
  hospitalIdOverride?: number | null
): UseMutationResult<PatientDetail, Error, { patientId: number; payload: UpdatePatientPayload }> {
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;

  return useMutation<PatientDetail, Error, { patientId: number; payload: UpdatePatientPayload }>({
    mutationFn: ({ patientId, payload }) => updatePatient(patientId, payload),
    onSuccess: (updatedPatient: PatientDetail) => {
      if (isPositiveIntegerId(hospitalId)) {
        queryClient.setQueryData(patientKeys.detail(hospitalId, updatedPatient.id), updatedPatient);
        queryClient.invalidateQueries({ queryKey: patientKeys.detail(hospitalId, updatedPatient.id) });
        queryClient.invalidateQueries({ queryKey: patientKeys.lists(hospitalId) });
        queryClient.invalidateQueries({ queryKey: ["hospital", "dashboard", hospitalId] });
      }
    },
  });
}
