import React from "react";
import { render, fireEvent, act } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider, UseQueryResult } from "@tanstack/react-query";
import { DashboardScreen } from "../screens/DashboardScreen";
import * as AuthContextModule from "../auth/AuthContext";
import * as useDashboardModule from "../api/useDashboard";
import { DashboardData } from "../api/dashboardApi";
import { tokenStorage } from "../storage/tokenStorage";

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

const mockDashboardData: DashboardData = {
  hospital: {
    id: 99,
    name: "St. Jude Memorial",
    license_number: "LIC-998877",
    city: "Dhaka",
    status: "active",
  },
  kpis: {
    total_patients: 42,
    total_requisitions: 88,
    pending_requisitions: 12,
    approved_requisitions: 50,
    dispensed_requisitions: 26,
  },
  recent_requisitions: [
    {
      id: 1,
      patient_name: "Alice Smith",
      blood_group: "O+",
      units_needed: 2,
      urgency_level: "urgent",
      status: "pending",
      created_at: "2026-09-14 09:30:00",
    },
    {
      id: 2,
      patient_name: "Bob Jones",
      blood_group: "B-",
      units_needed: 1,
      urgency_level: "routine",
      status: "approved",
      created_at: "2026-09-14 08:15:00",
    },
  ],
};

const createTestQueryClient = () =>
  new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  });

describe("DashboardScreen Component Tests", () => {
  beforeEach(() => {
    jest.restoreAllMocks();
    (tokenStorage.clearToken as jest.Mock).mockResolvedValue(true);
    (tokenStorage.setToken as jest.Mock).mockResolvedValue(true);

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

  test("1. Renders loading indicator when query is loading", async () => {
    jest.spyOn(useDashboardModule, "useDashboard").mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
    } as unknown as UseQueryResult<DashboardData, Error>);

    const queryClient = createTestQueryClient();
    const screen = await render(
      <QueryClientProvider client={queryClient}>
        <DashboardScreen />
      </QueryClientProvider>
    );

    expect(screen.getByTestId("dashboard-loading")).toBeTruthy();
    expect(screen.getByText("Loading operations dashboard...")).toBeTruthy();
  });

  test("2. Renders all hospital info, KPI grid, and recent requisitions", async () => {
    jest.spyOn(useDashboardModule, "useDashboard").mockReturnValue({
      data: mockDashboardData,
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
    } as unknown as UseQueryResult<DashboardData, Error>);

    const queryClient = createTestQueryClient();
    const screen = await render(
      <QueryClientProvider client={queryClient}>
        <DashboardScreen />
      </QueryClientProvider>
    );

    // Profile details
    expect(screen.getByTestId("user-name").props.children).toBe("Dr. Sarah Connor");
    expect(screen.getByTestId("user-email").props.children).toBe("sarah@cyberdyne-health.org");
    expect(screen.getByTestId("hospital-name").props.children).toBe("St. Jude Memorial");
    expect(screen.getByTestId("hospital-license").props.children).toBe("LIC-998877");
    expect(screen.getByTestId("hospital-city").props.children).toBe("Dhaka");
    expect(screen.getByTestId("hospital-status").props.children).toBe("ACTIVE");

    // KPI values
    expect(screen.getByTestId("kpi-total-patients").props.children).toBe(42);
    expect(screen.getByTestId("kpi-total-requisitions").props.children).toBe(88);
    expect(screen.getByTestId("kpi-pending-requisitions").props.children).toBe(12);
    expect(screen.getByTestId("kpi-approved-requisitions").props.children).toBe(50);
    expect(screen.getByTestId("kpi-dispensed-requisitions").props.children).toBe(26);

    // Recent requisitions list
    const items = screen.getAllByTestId("recent-requisition-item");
    expect(items.length).toBe(2);
    expect(screen.getByText("Alice Smith")).toBeTruthy();
    expect(screen.getByText("Bob Jones")).toBeTruthy();
  });

  test("3. Renders empty state when recent requisitions list is empty", async () => {
    jest.spyOn(useDashboardModule, "useDashboard").mockReturnValue({
      data: {
        ...mockDashboardData,
        recent_requisitions: [],
      },
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
    } as unknown as UseQueryResult<DashboardData, Error>);

    const queryClient = createTestQueryClient();
    const screen = await render(
      <QueryClientProvider client={queryClient}>
        <DashboardScreen />
      </QueryClientProvider>
    );

    expect(screen.getByTestId("empty-recent-requisitions")).toBeTruthy();
    expect(screen.getByText("No Requisitions Found")).toBeTruthy();
  });

  test("4. Renders error banner and retry button on query failure, triggering refetch on press", async () => {
    const mockRefetch = jest.fn().mockResolvedValue({} as UseQueryResult<DashboardData, Error>);
    const mockError = new Error("Unable to connect to the hospital server. Check your connection and try again.");
    (mockError as unknown as { isAxiosError: boolean }).isAxiosError = true;

    jest.spyOn(useDashboardModule, "useDashboard").mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: mockError,
      refetch: mockRefetch,
      isRefetching: false,
    } as unknown as UseQueryResult<DashboardData, Error>);

    const queryClient = createTestQueryClient();
    const screen = await render(
      <QueryClientProvider client={queryClient}>
        <DashboardScreen />
      </QueryClientProvider>
    );

    expect(screen.getByTestId("dashboard-error-banner")).toBeTruthy();
    expect(
      screen.getByText("Unable to connect to the hospital server. Check your connection and try again.")
    ).toBeTruthy();

    const retryButton = screen.getByTestId("dashboard-retry-button");
    await act(async () => {
      fireEvent.press(retryButton);
    });

    expect(mockRefetch).toHaveBeenCalled();
  });
});
