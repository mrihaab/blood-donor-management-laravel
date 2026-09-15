import { useQuery, useMutation, useQueryClient, UseQueryResult, UseMutationResult } from "@tanstack/react-query";
import {
  fetchRequisitions,
  fetchRequisitionById,
  createRequisition,
} from "./requisitionApi";
import {
  RequisitionListQuery,
  RequisitionListResponse,
  RequisitionDetail,
  CreateRequisitionPayload,
  RequisitionStatus,
} from "../types/requisition";
import { useAuth } from "../auth/AuthContext";
import { patientKeys } from "./usePatients";

export interface NormalizedRequisitionListQuery {
  page: number;
  per_page: number;
  status?: string;
}

export function isPositiveIntegerId(id: unknown): id is number {
  return typeof id === "number" && Number.isInteger(id) && id > 0;
}

function isValidStatus(status: unknown): status is RequisitionStatus {
  return (
    status === "pending" ||
    status === "approved" ||
    status === "rejected" ||
    status === "dispensed" ||
    status === "cancelled" ||
    status === "completed"
  );
}

export function normalizeRequisitionListQuery(query?: RequisitionListQuery): NormalizedRequisitionListQuery {
  const page = typeof query?.page === "number" && Number.isInteger(query.page) && query.page > 0
    ? query.page
    : 1;
  const per_page = typeof query?.per_page === "number" && Number.isInteger(query.per_page) && query.per_page > 0
    ? query.per_page
    : 15;

  const normalized: NormalizedRequisitionListQuery = { page, per_page };

  if (query?.status && query.status.trim() !== "") {
    const trimmed = query.status.trim();
    if (isValidStatus(trimmed)) {
      normalized.status = trimmed;
    }
  }

  return normalized;
}

export const requisitionKeys = {
  root: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "requisitions"] as const,
  lists: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "requisitions", "list"] as const,
  list: (hospitalId: number | null | undefined, query?: NormalizedRequisitionListQuery) =>
    ["hospital", hospitalId, "requisitions", "list", query] as const,
  details: (hospitalId: number | null | undefined) => ["hospital", hospitalId, "requisitions", "detail"] as const,
  detail: (hospitalId: number | null | undefined, requisitionId: number | null | undefined) =>
    ["hospital", hospitalId, "requisitions", "detail", requisitionId] as const,
};

export const requisitionRetryPolicy = (failureCount: number, error: unknown): boolean => {
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

export function useRequisitionsQuery(
  hospitalIdOverride?: number | null,
  query?: RequisitionListQuery
): UseQueryResult<RequisitionListResponse, Error> {
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;
  const enabled = isPositiveIntegerId(hospitalId);
  const normalizedQuery = normalizeRequisitionListQuery(query);

  return useQuery<RequisitionListResponse, Error>({
    queryKey: requisitionKeys.list(hospitalId, normalizedQuery),
    queryFn: () => fetchRequisitions(normalizedQuery),
    enabled,
    retry: requisitionRetryPolicy,
    staleTime: 10000,
  });
}

export function useRequisitionDetailQuery(
  requisitionId: number | null | undefined,
  hospitalIdOverride?: number | null
): UseQueryResult<RequisitionDetail, Error> {
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;
  const enabled = isPositiveIntegerId(hospitalId) && isPositiveIntegerId(requisitionId);

  return useQuery<RequisitionDetail, Error>({
    queryKey: requisitionKeys.detail(hospitalId, requisitionId),
    queryFn: () => fetchRequisitionById(requisitionId!),
    enabled,
    retry: requisitionRetryPolicy,
    staleTime: 10000,
  });
}

export function useCreateRequisitionMutation(
  hospitalIdOverride?: number | null
): UseMutationResult<RequisitionDetail, Error, CreateRequisitionPayload> {
  const queryClient = useQueryClient();
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;

  return useMutation<RequisitionDetail, Error, CreateRequisitionPayload>({
    mutationFn: (payload: CreateRequisitionPayload) => {
      if (!isPositiveIntegerId(hospitalId)) {
        throw new Error("Hospital context is not initialized");
      }
      return createRequisition(payload);
    },
    onSuccess: (newRequisition) => {
      queryClient.invalidateQueries({
        queryKey: requisitionKeys.root(hospitalId),
      });
      queryClient.invalidateQueries({
        queryKey: patientKeys.root(hospitalId),
      });
      if (newRequisition.patient_id) {
        queryClient.invalidateQueries({
          queryKey: patientKeys.detail(hospitalId, newRequisition.patient_id),
        });
      }
    },
  });
}
