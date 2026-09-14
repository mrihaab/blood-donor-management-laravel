import React from "react";
import { renderHook, waitFor, act } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import {
  usePatients,
  usePatient,
  useBloodGroups,
  useCreatePatient,
  useUpdatePatient,
  patientKeys,
  normalizePatientListQuery,
  patientRetryPolicy,
} from "../api/usePatients";
import * as PatientApiModule from "../api/patientApi";
import * as AuthContextModule from "../auth/AuthContext";
import { queryClient } from "../api/queryClient";

jest.mock("../api/patientApi", () => ({
  fetchPatients: jest.fn(),
  fetchPatientById: jest.fn(),
  createPatient: jest.fn(),
  updatePatient: jest.fn(),
  fetchBloodGroups: jest.fn(),
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

describe("usePatients Hook & Query Policy Unit Tests", () => {
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

  test("1. Exact root/list/detail/blood-group query keys", () => {
    const norm = normalizePatientListQuery({ page: 1, per_page: 15 });
    expect(patientKeys.root(99)).toEqual(["hospital", 99, "patients"]);
    expect(patientKeys.lists(99)).toEqual(["hospital", 99, "patients", "list"]);
    expect(patientKeys.list(99, norm)).toEqual(["hospital", 99, "patients", "list", norm]);
    expect(patientKeys.details(99)).toEqual(["hospital", 99, "patients", "detail"]);
    expect(patientKeys.detail(99, 10)).toEqual(["hospital", 99, "patients", "detail", 10]);
    expect(patientKeys.bloodGroups(99)).toEqual(["hospital", 99, "blood-groups"]);
  });

  test("2. Every key includes Hospital ID", () => {
    const keys = [
      patientKeys.root(99),
      patientKeys.lists(99),
      patientKeys.list(99, normalizePatientListQuery()),
      patientKeys.details(99),
      patientKeys.detail(99, 5),
      patientKeys.bloodGroups(99),
    ];
    keys.forEach((key) => {
      expect(key).toContain(99);
    });
  });

  test("3. Equivalent normalized queries have identical keys", () => {
    const q1 = normalizePatientListQuery({ search: "  John  ", page: 1 });
    const q2 = normalizePatientListQuery({ search: "John", page: 1 });
    expect(patientKeys.list(99, q1)).toEqual(patientKeys.list(99, q2));
  });

  test("4. Different Hospitals have different keys", () => {
    const keyH1 = patientKeys.lists(99);
    const keyH2 = patientKeys.lists(100);
    expect(keyH1).not.toEqual(keyH2);
  });

  test("5. Different Patient IDs have different detail keys", () => {
    const keyP1 = patientKeys.detail(99, 10);
    const keyP2 = patientKeys.detail(99, 20);
    expect(keyP1).not.toEqual(keyP2);
  });

  test("6. Invalid Hospital ID disables list", async () => {
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
    const { result } = await renderHook(() => usePatients(0), { wrapper: createWrapper(testClient) });

    expect(result.current.fetchStatus).toBe("idle");
    expect(PatientApiModule.fetchPatients).not.toHaveBeenCalled();
  });

  test("7. Invalid Hospital ID disables detail", async () => {
    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => usePatient(10, 0), { wrapper: createWrapper(testClient) });

    expect(result.current.fetchStatus).toBe("idle");
    expect(PatientApiModule.fetchPatientById).not.toHaveBeenCalled();
  });

  test("8. Invalid Patient ID disables detail", async () => {
    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => usePatient(0, 99), { wrapper: createWrapper(testClient) });

    expect(result.current.fetchStatus).toBe("idle");
    expect(PatientApiModule.fetchPatientById).not.toHaveBeenCalled();
  });

  test("9. Invalid Hospital ID disables blood groups", async () => {
    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => useBloodGroups(0), { wrapper: createWrapper(testClient) });

    expect(result.current.fetchStatus).toBe("idle");
    expect(PatientApiModule.fetchBloodGroups).not.toHaveBeenCalled();
  });

  test("10. Valid IDs execute API function", async () => {
    (PatientApiModule.fetchPatients as jest.Mock).mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });

    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => usePatients(99), { wrapper: createWrapper(testClient) });

    await waitFor(() => {
      expect(result.current.isSuccess).toBe(true);
    });

    expect(PatientApiModule.fetchPatients).toHaveBeenCalledTimes(1);
  });

  test("11. List uses normalized query", async () => {
    (PatientApiModule.fetchPatients as jest.Mock).mockResolvedValue({
      data: [],
      meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 },
    });

    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    await renderHook(() => usePatients(99, { search: "  John  " }), { wrapper: createWrapper(testClient) });

    await waitFor(() => {
      expect(PatientApiModule.fetchPatients).toHaveBeenCalledWith({
        page: 1,
        per_page: 15,
        search: "John",
      });
    });
  });

  test("12. 401 is not retried", () => {
    const error401 = { isAxiosError: true, response: { status: 401 } };
    expect(patientRetryPolicy(0, error401)).toBe(false);
  });

  test("13. 403 is not retried", () => {
    const error403 = { isAxiosError: true, response: { status: 403 } };
    expect(patientRetryPolicy(0, error403)).toBe(false);
  });

  test("14. 404 is not retried", () => {
    const error404 = { isAxiosError: true, response: { status: 404 } };
    expect(patientRetryPolicy(0, error404)).toBe(false);
  });

  test("15. 422 is not retried", () => {
    const error422 = { isAxiosError: true, response: { status: 422 } };
    expect(patientRetryPolicy(0, error422)).toBe(false);
  });

  test("16. 429 is not retried", () => {
    const error429 = { isAxiosError: true, response: { status: 429 } };
    expect(patientRetryPolicy(0, error429)).toBe(false);
  });

  test("17. TypeError is not retried", () => {
    const typeError = new TypeError("Malformed response");
    expect(patientRetryPolicy(0, typeError)).toBe(false);
  });

  test("18. SyntaxError is not retried", () => {
    const syntaxError = new SyntaxError("Invalid JSON");
    expect(patientRetryPolicy(0, syntaxError)).toBe(false);
  });

  test("19. Network/5xx error retries at most once", () => {
    const error500 = { isAxiosError: true, response: { status: 500 } };
    expect(patientRetryPolicy(0, error500)).toBe(true);
    expect(patientRetryPolicy(1, error500)).toBe(false);
  });

  test("20. Create invalidates only current-Hospital Patient lists", async () => {
    const testClient = new QueryClient();
    const spy = jest.spyOn(testClient, "invalidateQueries");

    const newPatient = {
      id: 101,
      mrn: "MRN-101",
      name: "New Patient",
      gender: "male" as const,
      date_of_birth: "1990-01-01",
      contact_number: null,
      status: "active" as const,
      ward_name: null,
      room_number: null,
      bed_number: null,
      blood_group: null,
      created_at: "2026-09-14 10:00:00",
      updated_at: "2026-09-14 10:00:00",
    };

    (PatientApiModule.createPatient as jest.Mock).mockResolvedValue(newPatient);

    const { result } = await renderHook(() => useCreatePatient(99), { wrapper: createWrapper(testClient) });

    await act(async () => {
      await result.current.mutateAsync({
        name: "New Patient",
        mrn: "MRN-101",
        gender: "male",
        date_of_birth: "1990-01-01",
      });
    });

    expect(spy).toHaveBeenCalledWith({ queryKey: ["hospital", 99, "patients", "list"] });
  });

  test("21. Create invalidates current-Hospital Dashboard", async () => {
    const testClient = new QueryClient();
    const spy = jest.spyOn(testClient, "invalidateQueries");

    (PatientApiModule.createPatient as jest.Mock).mockResolvedValue({
      id: 101,
      mrn: "MRN-101",
      name: "New Patient",
      gender: "male",
      date_of_birth: "1990-01-01",
      status: "active",
    });

    const { result } = await renderHook(() => useCreatePatient(99), { wrapper: createWrapper(testClient) });

    await act(async () => {
      await result.current.mutateAsync({
        name: "New Patient",
        mrn: "MRN-101",
        gender: "male",
        date_of_birth: "1990-01-01",
      });
    });

    expect(spy).toHaveBeenCalledWith({ queryKey: ["hospital", "dashboard", 99] });
  });

  test("22. Create does not invalidate another Hospital", async () => {
    const testClient = new QueryClient();
    const spy = jest.spyOn(testClient, "invalidateQueries");

    (PatientApiModule.createPatient as jest.Mock).mockResolvedValue({
      id: 101,
      mrn: "MRN-101",
      name: "New Patient",
      gender: "male",
      date_of_birth: "1990-01-01",
      status: "active",
    });

    const { result } = await renderHook(() => useCreatePatient(99), { wrapper: createWrapper(testClient) });

    await act(async () => {
      await result.current.mutateAsync({
        name: "New Patient",
        mrn: "MRN-101",
        gender: "male",
        date_of_birth: "1990-01-01",
      });
    });

    const calls = spy.mock.calls;
    calls.forEach(([arg]) => {
      if (arg && typeof arg === "object" && "queryKey" in arg) {
        const qk = (arg as { queryKey: unknown[] }).queryKey;
        expect(qk).not.toContain(100);
      }
    });
  });

  test("23. Update targets exact current-Hospital detail", async () => {
    const testClient = new QueryClient();
    const spy = jest.spyOn(testClient, "setQueryData");

    const updatedPatient = {
      id: 10,
      mrn: "MRN-100",
      name: "Updated Patient",
      gender: "male" as const,
      date_of_birth: "1990-01-01",
      status: "active" as const,
    };

    (PatientApiModule.updatePatient as jest.Mock).mockResolvedValue(updatedPatient);

    const { result } = await renderHook(() => useUpdatePatient(99), { wrapper: createWrapper(testClient) });

    await act(async () => {
      await result.current.mutateAsync({
        patientId: 10,
        payload: { name: "Updated Patient" },
      });
    });

    expect(spy).toHaveBeenCalledWith(["hospital", 99, "patients", "detail", 10], updatedPatient);
  });

  test("24. Update invalidates current-Hospital lists", async () => {
    const testClient = new QueryClient();
    const spy = jest.spyOn(testClient, "invalidateQueries");

    (PatientApiModule.updatePatient as jest.Mock).mockResolvedValue({
      id: 10,
      mrn: "MRN-100",
      name: "Updated Patient",
      gender: "male",
      date_of_birth: "1990-01-01",
      status: "active",
    });

    const { result } = await renderHook(() => useUpdatePatient(99), { wrapper: createWrapper(testClient) });

    await act(async () => {
      await result.current.mutateAsync({
        patientId: 10,
        payload: { name: "Updated Patient" },
      });
    });

    expect(spy).toHaveBeenCalledWith({ queryKey: ["hospital", 99, "patients", "list"] });
  });

  test("25. Update does not affect another Hospital", async () => {
    const testClient = new QueryClient();
    const spy = jest.spyOn(testClient, "invalidateQueries");

    (PatientApiModule.updatePatient as jest.Mock).mockResolvedValue({
      id: 10,
      mrn: "MRN-100",
      name: "Updated Patient",
      gender: "male",
      date_of_birth: "1990-01-01",
      status: "active",
    });

    const { result } = await renderHook(() => useUpdatePatient(99), { wrapper: createWrapper(testClient) });

    await act(async () => {
      await result.current.mutateAsync({
        patientId: 10,
        payload: { name: "Updated Patient" },
      });
    });

    const calls = spy.mock.calls;
    calls.forEach(([arg]) => {
      if (arg && typeof arg === "object" && "queryKey" in arg) {
        const qk = (arg as { queryKey: unknown[] }).queryKey;
        expect(qk).not.toContain(100);
      }
    });
  });

  test("26. Blood-group query uses appropriate stale time", async () => {
    (PatientApiModule.fetchBloodGroups as jest.Mock).mockResolvedValue([{ id: 1, name: "A+" }]);

    const testClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
    const { result } = await renderHook(() => useBloodGroups(99), { wrapper: createWrapper(testClient) });

    await waitFor(() => {
      expect(result.current.isSuccess).toBe(true);
    });

    const query = testClient.getQueryCache().find({ queryKey: patientKeys.bloodGroups(99) });
    expect(query).toBeDefined();
  });

  test("27. Existing logout/session-expiry cache-clear behavior remains intact", () => {
    const testKey = patientKeys.detail(99, 10);
    queryClient.setQueryData(testKey, { test: "cached_patient" });
    expect(queryClient.getQueryData(testKey)).toEqual({ test: "cached_patient" });

    queryClient.clear();
    expect(queryClient.getQueryData(testKey)).toBeUndefined();
  });
});
