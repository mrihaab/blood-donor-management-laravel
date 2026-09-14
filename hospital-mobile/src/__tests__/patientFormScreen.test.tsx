import React from "react";
import { render, fireEvent, waitFor, act } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { Alert } from "react-native";
import { PatientFormScreen } from "../screens/PatientFormScreen";
import * as UsePatientsModule from "../api/usePatients";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/usePatients", () => {
  const original = jest.requireActual("../api/usePatients");
  return {
    ...original,
    usePatient: jest.fn(),
    useBloodGroups: jest.fn(),
    useCreatePatient: jest.fn(),
    useUpdatePatient: jest.fn(),
  };
});

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn().mockResolvedValue(true),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

const mockDispatch = jest.fn();
let beforeRemoveListener: ((e: { preventDefault: () => void; data: { action: unknown } }) => void) | null = null;
const mockUnsubscribe = jest.fn();

const mockNavigation = {
  navigate: jest.fn(),
  replace: jest.fn(),
  goBack: jest.fn(),
  dispatch: mockDispatch,
  addListener: jest.fn((event: string, callback: (e: { preventDefault: () => void; data: { action: unknown } }) => void) => {
    if (event === "beforeRemove") {
      beforeRemoveListener = callback;
    }
    return mockUnsubscribe;
  }),
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

describe("PatientFormScreen Unit Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();
    beforeRemoveListener = null;

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

    (UsePatientsModule.useBloodGroups as jest.Mock).mockReturnValue({
      data: [{ id: 1, name: "A+" }, { id: 2, name: "B+" }],
      isLoading: false,
    });

    (UsePatientsModule.useCreatePatient as jest.Mock).mockReturnValue({
      mutateAsync: jest.fn(),
      isPending: false,
    });

    (UsePatientsModule.useUpdatePatient as jest.Mock).mockReturnValue({
      mutateAsync: jest.fn(),
      isPending: false,
    });

    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: false,
    });
  });

  test("1. Create mode renders inputs and MRN is editable", async () => {
    const route = { name: "PatientCreate", params: undefined };
    const { getByTestId, queryByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("input-name")).toBeTruthy();
    expect(getByTestId("input-mrn")).toBeTruthy();
    expect(queryByTestId("read-only-mrn")).toBeNull();
  });

  test("2. Edit mode loads existing patient and MRN is read-only without marking form dirty", async () => {
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
    };

    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: sampleDetail,
      isLoading: false,
      isError: false,
    });

    const route = { name: "PatientEdit", params: { patientId: 10 } };
    const { getByTestId, queryByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("read-only-mrn")).toBeTruthy();
    expect(queryByTestId("input-mrn")).toBeNull();
    expect(getByTestId("input-name").props.value).toBe("John Doe");

    // Clean edit form does not trigger Alert on beforeRemove
    const alertSpy = jest.spyOn(Alert, "alert");
    const preventDefault = jest.fn();
    if (beforeRemoveListener) {
      beforeRemoveListener({ preventDefault, data: { action: { type: "GO_BACK" } } });
    }
    expect(preventDefault).not.toHaveBeenCalled();
    expect(alertSpy).not.toHaveBeenCalled();
    alertSpy.mockRestore();
  });

  test("3. Validation rejects empty name, mrn, gender, and invalid DOB", async () => {
    const route = { name: "PatientCreate", params: undefined };
    const { getByTestId, getByText } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    expect(getByText("Patient full name is required.")).toBeTruthy();
    expect(getByText("MRN is required.")).toBeTruthy();
    expect(getByText("Please select a gender.")).toBeTruthy();
    expect(getByText("Date of birth is required.")).toBeTruthy();
  });

  test("4. Invalid DOB format and future DOB are rejected", async () => {
    const route = { name: "PatientCreate", params: undefined };
    const { getByTestId, getByText } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.changeText(getByTestId("input-name"), "John");
      fireEvent.changeText(getByTestId("input-mrn"), "MRN-100");
      fireEvent.press(getByTestId("gender-male"));
      fireEvent.changeText(getByTestId("input-dob"), "1990/01/01");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    expect(getByText("Date of birth must match YYYY-MM-DD format.")).toBeTruthy();

    await act(async () => {
      fireEvent.changeText(getByTestId("input-dob"), "2099-01-01");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    expect(getByText("Date of birth must be earlier than today.")).toBeTruthy();
  });

  test("5. Valid create form submits exact payload and navigates to detail bypassing beforeRemove", async () => {
    const mockMutate = jest.fn().mockResolvedValue({ id: 105 });
    (UsePatientsModule.useCreatePatient as jest.Mock).mockReturnValue({
      mutateAsync: mockMutate,
      isPending: false,
    });

    const route = { name: "PatientCreate", params: undefined };
    const { getByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.changeText(getByTestId("input-name"), "Jane Doe");
      fireEvent.changeText(getByTestId("input-mrn"), "MRN-105");
      fireEvent.press(getByTestId("gender-female"));
      fireEvent.changeText(getByTestId("input-dob"), "1995-06-15");
      fireEvent.press(getByTestId("blood-group-chip-1"));
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    await waitFor(() => {
      expect(mockMutate).toHaveBeenCalledWith({
        name: "Jane Doe",
        mrn: "MRN-105",
        gender: "female",
        date_of_birth: "1995-06-15",
        blood_group_id: 1,
        contact_number: null,
        ward_name: null,
        room_number: null,
        bed_number: null,
      });
    });

    expect(mockNavigation.replace).toHaveBeenCalledWith("PatientDetail", { patientId: 105 });
  });

  test("6. Update mode submits exact update payload without MRN and goes back bypassing beforeRemove", async () => {
    const mockMutate = jest.fn().mockResolvedValue({ id: 10 });
    (UsePatientsModule.useUpdatePatient as jest.Mock).mockReturnValue({
      mutateAsync: mockMutate,
      isPending: false,
    });

    const sampleDetail = {
      id: 10,
      mrn: "MRN-100",
      name: "John Old",
      gender: "male" as const,
      date_of_birth: "1990-01-01",
      status: "active" as const,
    };

    (UsePatientsModule.usePatient as jest.Mock).mockReturnValue({
      data: sampleDetail,
      isLoading: false,
      isError: false,
    });

    const route = { name: "PatientEdit", params: { patientId: 10 } };
    const { getByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.changeText(getByTestId("input-name"), "John Updated");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    await waitFor(() => {
      expect(mockMutate).toHaveBeenCalledWith({
        patientId: 10,
        payload: expect.objectContaining({
          name: "John Updated",
          gender: "male",
          date_of_birth: "1990-01-01",
        }),
      });
    });

    const payload = mockMutate.mock.calls[0][0].payload;
    expect(payload).not.toHaveProperty("mrn");
    expect(payload).not.toHaveProperty("status");

    expect(mockNavigation.goBack).toHaveBeenCalled();
  });

  test("7. Clean form exit does not prompt Alert", async () => {
    const alertSpy = jest.spyOn(Alert, "alert");
    const route = { name: "PatientCreate", params: undefined };

    const { getByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    fireEvent.press(getByTestId("cancel-button"));

    expect(mockNavigation.goBack).toHaveBeenCalled();
    expect(alertSpy).not.toHaveBeenCalled();

    alertSpy.mockRestore();
  });

  test("8. Dirty form triggers beforeRemove interception, Stay option dispatches 0 times, Discard dispatches once", async () => {
    const alertSpy = jest.spyOn(Alert, "alert");
    const route = { name: "PatientCreate", params: undefined };

    const { getByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.changeText(getByTestId("input-name"), "Changes Made");
    });

    const preventDefault = jest.fn();
    const mockAction = { type: "POP" };

    if (beforeRemoveListener) {
      beforeRemoveListener({ preventDefault, data: { action: mockAction } });
    }

    expect(preventDefault).toHaveBeenCalled();
    expect(alertSpy).toHaveBeenCalledWith(
      "Discard Unsaved Changes?",
      expect.any(String),
      expect.any(Array)
    );

    const buttons = alertSpy.mock.calls[0][2] as any[];
    const stayBtn = buttons.find((b) => b.text === "Stay");
    const discardBtn = buttons.find((b) => b.text === "Discard");

    expect(stayBtn).toBeTruthy();
    expect(discardBtn).toBeTruthy();

    // Stay option dispatches 0 times
    stayBtn.onPress?.();
    expect(mockDispatch).not.toHaveBeenCalled();

    // Discard option dispatches action exactly once
    discardBtn.onPress?.();
    expect(mockDispatch).toHaveBeenCalledTimes(1);
    expect(mockDispatch).toHaveBeenCalledWith(mockAction);

    alertSpy.mockRestore();
  });

  test("9. Pending submit blocks beforeRemove navigation without prompt", async () => {
    const alertSpy = jest.spyOn(Alert, "alert");
    const route = { name: "PatientCreate", params: undefined };

    // Slow mutation that stays pending
    const slowMutate = jest.fn().mockReturnValue(new Promise(() => {}));
    (UsePatientsModule.useCreatePatient as jest.Mock).mockReturnValue({
      mutateAsync: slowMutate,
      isPending: true,
    });

    const { getByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.changeText(getByTestId("input-name"), "Pending Submit");
      fireEvent.changeText(getByTestId("input-mrn"), "MRN-PENDING");
      fireEvent.press(getByTestId("gender-male"));
      fireEvent.changeText(getByTestId("input-dob"), "1990-01-01");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    const preventDefault = jest.fn();
    if (beforeRemoveListener) {
      beforeRemoveListener({ preventDefault, data: { action: { type: "GO_BACK" } } });
    }

    expect(preventDefault).toHaveBeenCalled();
    expect(alertSpy).not.toHaveBeenCalled();

    alertSpy.mockRestore();
  });

  test("10. Failed submit retains dirty prompt protection", async () => {
    const alertSpy = jest.spyOn(Alert, "alert");
    const mockMutate = jest.fn().mockRejectedValue({
      isAxiosError: true,
      response: { status: 422, data: { message: "Validation error" } },
    });

    (UsePatientsModule.useCreatePatient as jest.Mock).mockReturnValue({
      mutateAsync: mockMutate,
      isPending: false,
    });

    const route = { name: "PatientCreate", params: undefined };
    const { getByTestId } = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.changeText(getByTestId("input-name"), "Invalid Data");
      fireEvent.changeText(getByTestId("input-mrn"), "MRN-FAIL");
      fireEvent.press(getByTestId("gender-male"));
      fireEvent.changeText(getByTestId("input-dob"), "1990-01-01");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-button"));
    });

    // After failure, form remains dirty and beforeRemove prompts Alert
    const preventDefault = jest.fn();
    if (beforeRemoveListener) {
      beforeRemoveListener({ preventDefault, data: { action: { type: "GO_BACK" } } });
    }

    expect(preventDefault).toHaveBeenCalled();
    expect(alertSpy).toHaveBeenCalledWith(
      "Discard Unsaved Changes?",
      expect.any(String),
      expect.any(Array)
    );

    alertSpy.mockRestore();
  });

  test("11. Unmounting cleans up beforeRemove listener", async () => {
    const route = { name: "PatientCreate", params: undefined };

    const screen = await render(
      <PatientFormScreen navigation={mockNavigation as any} route={route as any} />,
      { wrapper: createWrapper() }
    );

    expect(mockNavigation.addListener).toHaveBeenCalledWith("beforeRemove", expect.any(Function));

    screen.unmount();
  });
});
