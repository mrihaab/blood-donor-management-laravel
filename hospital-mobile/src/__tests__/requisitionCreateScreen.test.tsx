import React from "react";
import { render, fireEvent, waitFor, act } from "@testing-library/react-native";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { RequisitionCreateScreen } from "../screens/RequisitionCreateScreen";
import * as UsePatientsModule from "../api/usePatients";
import * as UseRequisitionsModule from "../api/useRequisitions";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/usePatients", () => ({
  usePatients: jest.fn(),
}));

jest.mock("../api/useRequisitions", () => ({
  useCreateRequisitionMutation: jest.fn(),
}));

const mockNavigation: any = {
  navigate: jest.fn(),
  goBack: jest.fn(),
  addListener: jest.fn(() => jest.fn()),
  dispatch: jest.fn(),
};

function createWrapper() {
  const queryClient = new QueryClient({ defaultOptions: { queries: { retry: false } } });
  return function Wrapper({ children }: { children: React.ReactNode }) {
    return <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>;
  };
}

describe("RequisitionCreateScreen UI & Validation Unit Tests", () => {
  const mockMutateAsync = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();

    jest.spyOn(AuthContextModule, "useAuth").mockReturnValue({
      authState: "authenticated",
      token: "valid_token",
      user: { id: 1, hospital: { id: 99 } } as any,
      isLoading: false,
      isAuthenticated: true,
      login: jest.fn(),
      logout: jest.fn(),
      setAuthenticated: jest.fn(),
      setUnauthenticated: jest.fn(),
    });

    (UsePatientsModule.usePatients as jest.Mock).mockReturnValue({
      data: {
        data: [
          {
            id: 10,
            mrn: "MRN-001",
            name: "Patient Alpha",
            blood_group: { id: 1, name: "A+" },
            status: "active",
          },
        ],
      },
      isLoading: false,
    });

    (UseRequisitionsModule.useCreateRequisitionMutation as jest.Mock).mockReturnValue({
      mutateAsync: mockMutateAsync,
      isPending: false,
    });
  });

  test("1. Renders form inputs and patient picker", async () => {
    const { getByTestId } = await render(
      <RequisitionCreateScreen navigation={mockNavigation} route={{ params: {} } as any} />,
      { wrapper: createWrapper() }
    );

    expect(getByTestId("requisition-form-container")).toBeTruthy();
    expect(getByTestId("patient-picker")).toBeTruthy();
    expect(getByTestId("units-input")).toBeTruthy();
    expect(getByTestId("submit-requisition-btn")).toBeTruthy();
  });

  test("2. Client validation prevents submit when patient is unselected", async () => {
    const { getByTestId, getByText } = await render(
      <RequisitionCreateScreen navigation={mockNavigation} route={{ params: {} } as any} />,
      { wrapper: createWrapper() }
    );

    fireEvent.press(getByTestId("submit-requisition-btn"));

    await waitFor(() => {
      expect(getByText("Please resolve the highlighted validation errors.")).toBeTruthy();
    });

    expect(mockMutateAsync).not.toHaveBeenCalled();
  });

  test("3. Selecting patient and blood group submits valid payload", async () => {
    mockMutateAsync.mockResolvedValue({ id: 100 });

    const { getByTestId, findByTestId } = await render(
      <RequisitionCreateScreen navigation={mockNavigation} route={{ params: {} } as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.press(getByTestId("patient-picker"));
    });

    const option = await findByTestId("patient-option-10");
    await act(async () => {
      fireEvent.press(option);
    });

    const bgChip = await findByTestId("blood-group-chip-A+");
    await act(async () => {
      fireEvent.press(bgChip);
    });

    const urgencyChip = await findByTestId("urgency-chip-urgent");
    await act(async () => {
      fireEvent.press(urgencyChip);
    });

    await act(async () => {
      fireEvent.changeText(getByTestId("reason-input"), "Emergency pre-op requisition");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-requisition-btn"));
    });

    await waitFor(() => {
      expect(mockMutateAsync).toHaveBeenCalledWith({
        patient_id: 10,
        blood_group: "A+",
        units_needed: 1,
        urgency_level: "urgent",
        reason: "Emergency pre-op requisition",
        required_by: undefined,
        ward_name: undefined,
        room_number: undefined,
        bed_number: undefined,
        attendant_name: undefined,
        attendant_phone: undefined,
      });
    });
  });

  test("4. Server 422 validation errors are rendered inline", async () => {
    const apiError: any = new Error("Validation failed");
    apiError.isAxiosError = true;
    apiError.response = {
      status: 422,
      data: {
        message: "The selected patient is invalid.",
        errors: {
          patient_id: ["The selected patient does not belong to your hospital."],
        },
      },
    };

    mockMutateAsync.mockRejectedValue(apiError);

    const { getByTestId, findByTestId, getByText } = await render(
      <RequisitionCreateScreen navigation={mockNavigation} route={{ params: {} } as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.press(getByTestId("patient-picker"));
    });

    const option = await findByTestId("patient-option-10");
    await act(async () => {
      fireEvent.press(option);
    });

    const bgChip = await findByTestId("blood-group-chip-O-");
    await act(async () => {
      fireEvent.press(bgChip);
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-requisition-btn"));
    });

    await waitFor(() => {
      expect(getByText("The selected patient does not belong to your hospital.")).toBeTruthy();
    });
  });

  test("5. Submitting all fields sends each parameter independently without concatenation", async () => {
    mockMutateAsync.mockResolvedValue({ id: 200 });

    const { getByTestId, findByTestId } = await render(
      <RequisitionCreateScreen navigation={mockNavigation} route={{ params: {} } as any} />,
      { wrapper: createWrapper() }
    );

    await act(async () => {
      fireEvent.press(getByTestId("patient-picker"));
    });

    const option = await findByTestId("patient-option-10");
    await act(async () => {
      fireEvent.press(option);
    });

    const bgChip = await findByTestId("blood-group-chip-A+");
    await act(async () => {
      fireEvent.press(bgChip);
    });

    await act(async () => {
      fireEvent.changeText(getByTestId("reason-input"), "REASON-ONLY-TEST");
      fireEvent.changeText(getByTestId("ward-name-input"), "WARD-ONLY-TEST");
      fireEvent.changeText(getByTestId("room-number-input"), "ROOM-ONLY-TEST");
      fireEvent.changeText(getByTestId("bed-number-input"), "BED-ONLY-TEST");
      fireEvent.changeText(getByTestId("attendant-name-input"), "ATTENDANT-ONLY-TEST");
      fireEvent.changeText(getByTestId("attendant-phone-input"), "PHONE-ONLY-TEST");
    });

    await act(async () => {
      fireEvent.press(getByTestId("submit-requisition-btn"));
    });

    await waitFor(() => {
      expect(mockMutateAsync).toHaveBeenCalledWith({
        patient_id: 10,
        blood_group: "A+",
        units_needed: 1,
        urgency_level: "routine",
        reason: "REASON-ONLY-TEST",
        ward_name: "WARD-ONLY-TEST",
        room_number: "ROOM-ONLY-TEST",
        bed_number: "BED-ONLY-TEST",
        attendant_name: "ATTENDANT-ONLY-TEST",
        attendant_phone: "PHONE-ONLY-TEST",
        required_by: undefined,
      });
    });
  });
});
