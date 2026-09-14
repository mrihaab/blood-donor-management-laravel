import React from "react";
import { renderHook, waitFor } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { useDashboard, dashboardQueryKey } from "../api/useDashboard";
import { getDashboardApi } from "../api/dashboardApi";
import * as AuthContextModule from "../auth/AuthContext";
import { queryClient } from "../api/queryClient";

jest.mock("../api/dashboardApi", () => ({
  getDashboardApi: jest.fn(),
}));

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn().mockResolvedValue(true),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

const mockUser = {
  id: 42,
  name: "Dr. Sarah Connor",
  email: "sarah@cyberdyne-health.org",
  role: "hospital_staff",
  hospital: {
    id: 99,
    name: "St. Jude Memorial",
    license_number: "LIC-998877",
    status: "active",
  },
};

function createWrapper(client: QueryClient) {
  return function Wrapper({ children }: { children: React.ReactNode }) {
    return <QueryClientProvider client={client}>{children}</QueryClientProvider>;
  };
}

describe("useDashboard Hook & Query Policy Unit Tests", () => {
  beforeEach(() => {
    jest.restoreAllMocks();
    (getDashboardApi as jest.Mock).mockReset();

    jest.spyOn(AuthContextModule, "useAuth").mockReturnValue({
      authState: "authenticated",
      token: "active_token_123",
      user: mockUser,
      isLoading: false,
      isAuthenticated: true,
      login: jest.fn(),
      logout: jest.fn(),
      setAuthenticated: jest.fn(),
      setUnauthenticated: jest.fn(),
    });
  });

  test("1. dashboardQueryKey matches exact ['hospital', 'dashboard', hospitalId] format", () => {
    expect(dashboardQueryKey(99)).toEqual(["hospital", "dashboard", 99]);
    expect(dashboardQueryKey(undefined)).toEqual(["hospital", "dashboard", undefined]);
    expect(dashboardQueryKey(null)).toEqual(["hospital", "dashboard", null]);
  });

  test("2. Query enables ONLY for positive integer hospital IDs", async () => {
    jest.spyOn(AuthContextModule, "useAuth").mockReturnValue({
      authState: "unauthenticated",
      token: null,
      user: null,
      isLoading: false,
      isAuthenticated: false,
      login: jest.fn(),
      logout: jest.fn(),
      setAuthenticated: jest.fn(),
      setUnauthenticated: jest.fn(),
    });

    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });

    // Test with undefined ID
    const hookUndefined = await renderHook(() => useDashboard(), { wrapper: createWrapper(testClient) });
    expect(hookUndefined.result.current.fetchStatus).toBe("idle");

    // Test with zero ID
    const hookZero = await renderHook(() => useDashboard(0), { wrapper: createWrapper(testClient) });
    expect(hookZero.result.current.fetchStatus).toBe("idle");

    // Test with negative ID
    const hookNegative = await renderHook(() => useDashboard(-5), { wrapper: createWrapper(testClient) });
    expect(hookNegative.result.current.fetchStatus).toBe("idle");

    expect(getDashboardApi).not.toHaveBeenCalled();
  });

  test("3. HTTP 429 and 4xx client errors NEVER retry", async () => {
    const error429 = { isAxiosError: true, response: { status: 429 } };
    (getDashboardApi as jest.Mock).mockRejectedValue(error429);

    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => useDashboard(99), { wrapper: createWrapper(testClient) });

    await waitFor(() => {
      expect(result.current.isError).toBe(true);
    });

    expect(getDashboardApi).toHaveBeenCalledTimes(1);
  });

  test("4. Malformed response / TypeError NEVER retries", async () => {
    const typeError = new TypeError("Cannot read properties of undefined (reading 'hospital')");
    (getDashboardApi as jest.Mock).mockRejectedValue(typeError);

    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => useDashboard(99), { wrapper: createWrapper(testClient) });

    await waitFor(() => {
      expect(result.current.isError).toBe(true);
    });

    expect(getDashboardApi).toHaveBeenCalledTimes(1);
  });

  test("5. Query cache clearing protects session boundaries", () => {
    const testKey = dashboardQueryKey(99);
    queryClient.setQueryData(testKey, { test: "cached_dashboard" });
    expect(queryClient.getQueryData(testKey)).toEqual({ test: "cached_dashboard" });

    queryClient.clear();
    expect(queryClient.getQueryData(testKey)).toBeUndefined();
  });
});
