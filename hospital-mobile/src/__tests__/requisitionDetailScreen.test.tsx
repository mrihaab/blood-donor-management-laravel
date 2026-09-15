import React from "react";
import { render, fireEvent } from "@testing-library/react-native";
import { RequisitionDetailScreen } from "../screens/RequisitionDetailScreen";
import * as UseRequisitionsModule from "../api/useRequisitions";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/useRequisitions", () => ({
  useRequisitionDetailQuery: jest.fn(),
}));

const mockNavigation: any = {
  navigate: jest.fn(),
  goBack: jest.fn(),
};

describe("RequisitionDetailScreen UI Unit Tests", () => {
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
  });

  const validDetail = {
    id: 15,
    patient_id: 10,
    patient_name: "Patient Alpha",
    blood_group: "A+",
    units_needed: 2,
    urgency_level: "urgent",
    status: "approved",
    required_by: "2026-09-20 12:00:00",
    reason: "Clinical reason for blood request",
    approved_at: "2026-09-15 09:00:00",
    rejected_at: null,
    created_at: "2026-09-15 08:00:00",
    updated_at: "2026-09-15 09:00:00",
    patient: { id: 10, mrn: "MRN-001", name: "Patient Alpha" },
  };

  test("1. Renders loading view when fetching detail", async () => {
    (UseRequisitionsModule.useRequisitionDetailQuery as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <RequisitionDetailScreen navigation={mockNavigation} route={{ params: { requisitionId: 15 } } as any} />
    );

    expect(getByTestId("requisition-detail-loading")).toBeTruthy();
  });

  test("2. Renders complete allowlisted requisition detail fields", async () => {
    (UseRequisitionsModule.useRequisitionDetailQuery as jest.Mock).mockReturnValue({
      data: validDetail,
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
    });

    const { getByTestId, getByText } = await render(
      <RequisitionDetailScreen navigation={mockNavigation} route={{ params: { requisitionId: 15 } } as any} />
    );

    expect(getByTestId("requisition-detail-container")).toBeTruthy();
    expect(getByText("Patient Alpha")).toBeTruthy();
    expect(getByText("MRN: MRN-001")).toBeTruthy();
    expect(getByText("APPROVED")).toBeTruthy();
    expect(getByText("URGENT")).toBeTruthy();
    expect(getByText("2 Units")).toBeTruthy();
    expect(getByText("Clinical reason for blood request")).toBeTruthy();
    expect(getByTestId("requisition-detail-timeline")).toBeTruthy();
  });

  test("3. Pressing View Patient Record navigates to PatientDetail", async () => {
    (UseRequisitionsModule.useRequisitionDetailQuery as jest.Mock).mockReturnValue({
      data: validDetail,
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
    });

    const { getByTestId } = await render(
      <RequisitionDetailScreen navigation={mockNavigation} route={{ params: { requisitionId: 15 } } as any} />
    );

    fireEvent.press(getByTestId("view-patient-profile-btn"));

    expect(mockNavigation.navigate).toHaveBeenCalledWith("PatientDetail", { patientId: 10 });
  });

  test("4. Renders 404/Error state on invalid ID or cross-hospital access failure", async () => {
    const mockRefetch = jest.fn();
    (UseRequisitionsModule.useRequisitionDetailQuery as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: new Error("Requisition not found"),
      refetch: mockRefetch,
    });

    const { getByTestId, getByText } = await render(
      <RequisitionDetailScreen navigation={mockNavigation} route={{ params: { requisitionId: 999 } } as any} />
    );

    expect(getByTestId("requisition-detail-error")).toBeTruthy();
    expect(getByText("Requisition Not Found")).toBeTruthy();

    fireEvent.press(getByTestId("retry-btn"));
    expect(mockRefetch).toHaveBeenCalled();
  });
});
