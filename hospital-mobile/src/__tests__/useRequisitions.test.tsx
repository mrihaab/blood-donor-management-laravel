import React from "react";
import { renderHook, waitFor, act } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import {
  useRequisitionsQuery,
  useRequisitionDetailQuery,
  useCreateRequisitionMutation,
  requisitionKeys,
  normalizeRequisitionListQuery,
  requisitionRetryPolicy,
} from "../api/useRequisitions";
import * as RequisitionApiModule from "../api/requisitionApi";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/requisitionApi", () => ({
  fetchRequisitions: jest.fn(),
  fetchRequisitionById: jest.fn(),
  createRequisition: jest.fn(),
}));

const mockUser = {
  id: 42,
  name: "Dr. Sarah Connor",
  email: "sarah@centralhosp.org",
  role: "hospital",
  hospital: {
    id: 99,
    name: "Central Clinical Hospital",
    license_number: "HOSP-01",
    status: "active",
  },
};

function createWrapper(client: QueryClient) {
  return function Wrapper({ children }: { children: React.ReactNode }) {
    return <QueryClientProvider client={client}>{children}</QueryClientProvider>;
  };
}

describe("useRequisitions React Query Hooks Unit Tests", () => {
  beforeEach(() => {
    jest.restoreAllMocks();
    jest.clearAllMocks();

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

  test("1. Requisition query keys are scoped to Hospital ID", () => {
    const norm = normalizeRequisitionListQuery({ status: "pending", page: 1 });
    expect(requisitionKeys.root(99)).toEqual(["hospital", 99, "requisitions"]);
    expect(requisitionKeys.lists(99)).toEqual(["hospital", 99, "requisitions", "list"]);
    expect(requisitionKeys.list(99, norm)).toEqual(["hospital", 99, "requisitions", "list", norm]);
    expect(requisitionKeys.details(99)).toEqual(["hospital", 99, "requisitions", "detail"]);
    expect(requisitionKeys.detail(99, 15)).toEqual(["hospital", 99, "requisitions", "detail", 15]);
  });

  test("2. Retry policy rejects structural errors and 401/403/404/422 status codes", () => {
    expect(requisitionRetryPolicy(1, new Error("Fail"))).toBe(false);
    expect(requisitionRetryPolicy(0, new TypeError("Parse Error"))).toBe(false);

    const error403 = { isAxiosError: true, response: { status: 403 } };
    expect(requisitionRetryPolicy(0, error403)).toBe(false);

    const error500 = { isAxiosError: true, response: { status: 500 } };
    expect(requisitionRetryPolicy(0, error500)).toBe(true);
  });

  test("3. useRequisitionsQuery calls fetchRequisitions when authenticated and enabled", async () => {
    (RequisitionApiModule.fetchRequisitions as jest.Mock).mockResolvedValue({
      data: [
        {
          id: 1,
          patient_name: "John Doe",
          blood_group: "A+",
          units_needed: 2,
          urgency_level: "urgent",
          status: "pending",
          created_at: null,
        },
      ],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
    });

    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    });

    const { result } = await renderHook(() => useRequisitionsQuery(99, { status: "pending" }), {
      wrapper: createWrapper(queryClient),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    expect(RequisitionApiModule.fetchRequisitions).toHaveBeenCalledWith(
      expect.objectContaining({ status: "pending" })
    );
    expect(result.current.data?.data[0].id).toBe(1);
  });

  test("4. useRequisitionDetailQuery calls fetchRequisitionById with valid ID", async () => {
    (RequisitionApiModule.fetchRequisitionById as jest.Mock).mockResolvedValue({
      id: 15,
      hospital_id: 99,
      hospital: "Central Clinical Hospital",
      city: "Metropolis",
      patient_name: "Patient Alpha",
      blood_group: "A+",
      units_needed: 2,
      urgency_level: "urgent",
      status: "pending",
      created_at: null,
    });

    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    });

    const { result } = await renderHook(() => useRequisitionDetailQuery(15, 99), {
      wrapper: createWrapper(queryClient),
    });

    await waitFor(() => expect(result.current.isSuccess).toBe(true));

    expect(RequisitionApiModule.fetchRequisitionById).toHaveBeenCalledWith(15);
    expect(result.current.data?.id).toBe(15);
  });

  test("5. useCreateRequisitionMutation triggers createRequisition and invalidates hospital keys", async () => {
    (RequisitionApiModule.createRequisition as jest.Mock).mockResolvedValue({
      id: 100,
      hospital_id: 99,
      patient_id: 10,
      patient_name: "Patient Alpha",
      blood_group: "O-",
      units_needed: 1,
      urgency_level: "emergency",
      status: "pending",
    });

    const queryClient = new QueryClient({
      defaultOptions: { queries: { retry: false } },
    });

    const invalidateSpy = jest.spyOn(queryClient, "invalidateQueries");

    const { result } = await renderHook(() => useCreateRequisitionMutation(99), {
      wrapper: createWrapper(queryClient),
    });

    await act(async () => {
      await result.current.mutateAsync({
        patient_id: 10,
        blood_group: "O-",
        units_needed: 1,
        urgency_level: "emergency",
      });
    });

    expect(RequisitionApiModule.createRequisition).toHaveBeenCalledWith(
      expect.objectContaining({ patient_id: 10, blood_group: "O-" })
    );

    expect(invalidateSpy).toHaveBeenCalledWith({
      queryKey: ["hospital", 99, "requisitions"],
    });
  });
});
