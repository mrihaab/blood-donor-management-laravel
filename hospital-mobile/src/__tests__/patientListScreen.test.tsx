import React from "react";
import { render, fireEvent, waitFor } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { PatientListScreen } from "../screens/PatientListScreen";
import * as UsePatientsModule from "../api/usePatients";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/usePatients", () => {
  const original = jest.requireActual("../api/usePatients");
  return {
    ...original,
    usePatients: jest.fn(),
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

describe("PatientListScreen Unit Tests", () => {
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

  const samplePatients = [
    {
      id: 10,
      mrn: "MRN-100",
      name: "Alice Smith",
      gender: "female" as const,
      status: "active" as const,
      ward_name: "Ward A",
      room_number: "101",
      bed_number: "1",
      blood_group: { id: 1, name: "A+" },
      created_at: "2026-09-14 10:00:00",
    },
  ];

  test("1. Header renders and Register action navigates correctly", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: samplePatients, meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByText, getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByText("Patients")).toBeTruthy();
    const registerBtn = getByTestId("register-patient-button");
    fireEvent.press(registerBtn);
    expect(mockNavigation.navigate).toHaveBeenCalledWith("PatientCreate");
  });

  test("2. Cards show list-approved fields and omit DOB/contact", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: samplePatients, meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId, queryByText } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("patient-name").children[0]).toBe("Alice Smith");
    expect(getByTestId("patient-mrn").children[1]).toBe("MRN-100");
    expect(queryByText("1990-01-01")).toBeNull();
    expect(queryByText("+1234567890")).toBeNull();
  });

  test("3. Card press navigates to PatientDetail with numeric ID", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: samplePatients, meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    fireEvent.press(getByTestId("patient-card"));
    expect(mockNavigation.navigate).toHaveBeenCalledWith("PatientDetail", { patientId: 10 });
  });

  test("4. Search input is debounced", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    const searchInput = getByTestId("search-input");
    fireEvent.changeText(searchInput, "Alice");

    await waitFor(
      () => {
        expect(UsePatientsModule.usePatients).toHaveBeenLastCalledWith(
          undefined,
          expect.objectContaining({ search: "Alice", page: 1 })
        );
      },
      { timeout: 1000 }
    );
  });

  test("5. Clear search resets query", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    fireEvent.changeText(getByTestId("search-input"), "Alice");
    await waitFor(() => expect(getByTestId("clear-search-button")).toBeTruthy());

    fireEvent.press(getByTestId("clear-search-button"));
    await waitFor(() => expect(getByTestId("search-input").props.value).toBe(""));
  });

  test("6. Status filters work for active, discharged, archived and All filter omits status", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    fireEvent.press(getByTestId("filter-active"));
    await waitFor(() => {
      expect(UsePatientsModule.usePatients).toHaveBeenLastCalledWith(
        undefined,
        expect.objectContaining({ status: "active" })
      );
    });

    fireEvent.press(getByTestId("filter-discharged"));
    await waitFor(() => {
      expect(UsePatientsModule.usePatients).toHaveBeenLastCalledWith(
        undefined,
        expect.objectContaining({ status: "discharged" })
      );
    });

    fireEvent.press(getByTestId("filter-archived"));
    await waitFor(() => {
      expect(UsePatientsModule.usePatients).toHaveBeenLastCalledWith(
        undefined,
        expect.objectContaining({ status: "archived" })
      );
    });

    fireEvent.press(getByTestId("filter-all"));
    await waitFor(() => {
      expect(UsePatientsModule.usePatients).toHaveBeenLastCalledWith(
        undefined,
        expect.objectContaining({ status: undefined })
      );
    });
  });

  test("7. Loading state renders ActivityIndicator", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("patient-list-loading")).toBeTruthy();
  });

  test("8. Empty Hospital state renders fallback message", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("empty-patient-list")).toBeTruthy();
  });

  test("9. Error state renders error banner with Retry", async () => {
    const mockRefetch = jest.fn();
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: new Error("Network error"),
      refetch: mockRefetch,
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("patient-list-error-banner")).toBeTruthy();

    fireEvent.press(getByTestId("patient-list-retry-button"));
    expect(mockRefetch).toHaveBeenCalled();
  });

  test("10. Pagination Previous and Next buttons work and respect bounds", async () => {
    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: { data: samplePatients, meta: { current_page: 1, last_page: 2, per_page: 15, total: 20 } },
      isLoading: false,
      isError: false,
      refetch: jest.fn(),
      isFetching: false,
    });

    const { getByTestId } = await render(
      <PatientListScreen navigation={mockNavigation as any} route={{} as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("prev-page-button")).toBeTruthy();

    const prevBtn = getByTestId("prev-page-button");
    const nextBtn = getByTestId("next-page-button");

    expect(prevBtn.props.accessibilityState?.disabled ?? prevBtn.props.disabled).toBe(true);
    expect(nextBtn.props.accessibilityState?.disabled ?? nextBtn.props.disabled).toBe(false);

    fireEvent.press(nextBtn);

    await waitFor(() => {
      expect(UsePatientsModule.usePatients).toHaveBeenLastCalledWith(
        undefined,
        expect.objectContaining({ page: 2 })
      );
    });
  });
});
