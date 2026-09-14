import React from "react";
import { render, fireEvent } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { PatientDetailScreen } from "../screens/PatientDetailScreen";
import * as UsePatientsModule from "../api/usePatients";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/usePatients", () => {
  const original = jest.requireActual("../api/usePatients");
  return {
    ...original,
    usePatient: jest.fn(),
  };
});

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn().mockResolvedValue(true),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

const mockNavigation = {
  navigate: jest.fn(),
  goBack: jest.fn(),
};

const mockUser = {
  id: 42,
  name: "Dr. Sarah",
  email: "sarah@example.com",
  role: "hospital_staff",
  hospital: { id: 99, name: "St. Jude", license_number: "LIC-99", status: "active" },
};

function createWrapper() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return function Wrapper({ children }: { children: React.ReactNode }) {
    return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>;
  };
}

describe("PatientDetailScreen Unit Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();

    jest.spyOn(AuthContextModule, "useAuth").mockReturnValue({
      authState: "authenticated",
      token: "active_token",
      user: mockUser,
      isLoading: false,
      isAuthenticated: true,
      login: jest.fn(),
      logout: jest.fn(),
      setAuthenticated: jest.fn(),
      setUnauthenticated: jest.fn(),
    });
  });

  const sampleDetail = {
    id: 10,
    mrn: "MRN-100",
    name: "John Doe",
    gender: "male" as const,
    date_of_birth: "1990-01-01",
    contact_number: "+1234567890",
    status: "active" as const,
    ward_name: "Ward A",
    room_number: "101",
    bed_number: "1",
    blood_group: { id: 1, name: "A+" },
    created_at: "2026-09-14 10:00:00",
    updated_at: "2026-09-14 10:00:00",
    blood_requests: [
      {
        id: 5,
        blood_group: "A+",
        units_needed: 2,
        urgency_level: "urgent",
        status: "pending",
        created_at: "2026-09-14 10:00:00",
      },
    ],
  };

  test("1. Route Patient ID is passed to hook and details render", async () => {
    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: sampleDetail,
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const route = { params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientDetailScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(UsePatientsModule.usePatient).toHaveBeenCalledWith(10);
    expect(getByTestId("patient-detail-name").children[0]).toBe("John Doe");
    expect(getByTestId("patient-detail-mrn").children[1]).toBe("MRN-100");
    expect(getByTestId("patient-detail-dob").children[0]).toBe("1990-01-01");
    expect(getByTestId("patient-detail-contact").children[0]).toBe("+1234567890");
    expect(getByTestId("patient-detail-blood-group").children[0]).toBe("A+");
  });

  test("2. Null contact and blood group fallbacks render safely", async () => {
    const detailWithNulls = {
      ...sampleDetail,
      contact_number: null,
      blood_group: null,
      ward_name: null,
      room_number: null,
      bed_number: null,
    };

    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: detailWithNulls,
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const route = { params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientDetailScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("patient-detail-contact").children[0]).toBe("Not provided");
    expect(getByTestId("patient-detail-blood-group").children[0]).toBe("Not recorded");
    expect(getByTestId("patient-detail-location").children[0]).toBe("Not assigned");
  });

  test("3. Edit button navigates to PatientEdit with numeric ID", async () => {
    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: sampleDetail,
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const route = { params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientDetailScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    fireEvent.press(getByTestId("edit-patient-button"));
    expect(mockNavigation.navigate).toHaveBeenCalledWith("PatientEdit", { patientId: 10 });
  });

  test("4. Loading state renders ActivityIndicator", async () => {
    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      refetch: jest.fn(),
    });

    const route = { params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientDetailScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("patient-detail-loading")).toBeTruthy();
  });

  test("5. Error state renders error banner with Retry", async () => {
    const mockRefetch = jest.fn();
    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: new Error("Patient not found"),
      refetch: mockRefetch,
    });

    const route = { params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientDetailScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("patient-detail-error-banner")).toBeTruthy();
    fireEvent.press(getByTestId("patient-detail-retry-button"));
    expect(mockRefetch).toHaveBeenCalled();
  });

  test("6. Empty requisition state renders fallback message", async () => {
    const detailNoReqs = { ...sampleDetail, blood_requests: [] };
    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: detailNoReqs,
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const route = { params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientDetailScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("empty-patient-requisitions")).toBeTruthy();
  });
});
