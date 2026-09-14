import { useQuery, UseQueryResult } from "@tanstack/react-query";
import { getDashboardApi, DashboardData } from "./dashboardApi";
import { useAuth } from "../auth/AuthContext";

export const dashboardQueryKey = (
  hospitalId: number | null | undefined,
) => ["hospital", "dashboard", hospitalId] as const;

export const DASHBOARD_QUERY_KEY = dashboardQueryKey;

export function useDashboard(hospitalIdOverride?: number | null): UseQueryResult<DashboardData, Error> {
  const { user } = useAuth();
  const hospitalId = hospitalIdOverride !== undefined ? hospitalIdOverride : user?.hospital?.id;

  const isPositiveIntegerId =
    typeof hospitalId === "number" && Number.isInteger(hospitalId) && hospitalId > 0;

  return useQuery<DashboardData, Error>({
    queryKey: dashboardQueryKey(hospitalId),
    queryFn: getDashboardApi,
    enabled: isPositiveIntegerId,
    retry: (failureCount, error) => {
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
    },
  });
}
