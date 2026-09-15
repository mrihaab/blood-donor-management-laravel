import React from "react";
import { render, fireEvent } from "@testing-library/react-native";
import { RequisitionListScreen } from "../screens/RequisitionListScreen";
import * as UseRequisitionsModule from "../api/useRequisitions";
import * as AuthContextModule from "../auth/AuthContext";

jest.mock("../api/useRequisitions", () => ({
  useRequisitionsQuery: jest.fn(),
}));

const mockNavigation: any = {
  navigate: jest.fn(),
  goBack: jest.fn(),
};

describe("RequisitionListScreen UI Unit Tests", () => {
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

  test("1. Renders loading indicator when fetching requisitions", async () => {
    (UseRequisitionsModule.useRequisitionsQuery as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: true,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
      isFetching: true,
    });

    const { getByTestId } = await render(
      <RequisitionListScreen navigation={mockNavigation} route={{} as any} />
    );

    expect(getByTestId("requisition-list-loading")).toBeTruthy();
  });

  test("2. Renders empty state when no requisitions match filter", async () => {
    (UseRequisitionsModule.useRequisitionsQuery as jest.Mock).mockReturnValue({
      data: { data: [], meta: { current_page: 1, last_page: 1, per_page: 15, total: 0 } },
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
      isFetching: false,
    });

    const { getByTestId, getByText } = await render(
      <RequisitionListScreen navigation={mockNavigation} route={{} as any} />
    );

    expect(getByTestId("requisition-list-empty")).toBeTruthy();
    expect(getByText("No Requisitions Found")).toBeTruthy();
  });

  test("3. Renders list of requisitions with status & urgency badges", async () => {
    (UseRequisitionsModule.useRequisitionsQuery as jest.Mock).mockReturnValue({
      data: {
        data: [
          {
            id: 10,
            patient_id: 1,
            patient_name: "Patient Alpha",
            blood_group: "A+",
            units_needed: 2,
            urgency_level: "urgent",
            status: "pending",
            created_at: "2026-09-15 08:00:00",
            patient: { id: 1, mrn: "MRN-001", name: "Patient Alpha" },
          },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
      },
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
      isFetching: false,
    });

    const { getByText } = await render(
      <RequisitionListScreen navigation={mockNavigation} route={{} as any} />
    );

    expect(getByText("Patient Alpha")).toBeTruthy();
    expect(getByText("MRN: MRN-001")).toBeTruthy();
    expect(getByText("A+")).toBeTruthy();
    expect(getByText("PENDING")).toBeTruthy();
    expect(getByText("URGENT")).toBeTruthy();
  });

  test("4. Pressing requisition card navigates to RequisitionDetail", async () => {
    (UseRequisitionsModule.useRequisitionsQuery as jest.Mock).mockReturnValue({
      data: {
        data: [
          {
            id: 10,
            patient_id: 1,
            patient_name: "Patient Alpha",
            blood_group: "A+",
            units_needed: 2,
            urgency_level: "urgent",
            status: "pending",
            created_at: "2026-09-15 08:00:00",
          },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
      },
      isLoading: false,
      isError: false,
      error: null,
      refetch: jest.fn(),
      isRefetching: false,
      isFetching: false,
    });

    const { getByTestId } = await render(
      <RequisitionListScreen navigation={mockNavigation} route={{} as any} />
    );

    fireEvent.press(getByTestId("requisition-card-10"));

    expect(mockNavigation.navigate).toHaveBeenCalledWith("RequisitionDetail", { requisitionId: 10 });
  });

  test("5. Renders error message and triggers refetch on Try Again press", async () => {
    const mockRefetch = jest.fn();
    (UseRequisitionsModule.useRequisitionsQuery as jest.Mock).mockReturnValue({
      data: undefined,
      isLoading: false,
      isError: true,
      error: new Error("Network Timeout"),
      refetch: mockRefetch,
      isRefetching: false,
      isFetching: false,
    });

    const { getByTestId } = await render(
      <RequisitionListScreen navigation={mockNavigation} route={{} as any} />
    );

    expect(getByTestId("requisition-list-error")).toBeTruthy();

    fireEvent.press(getByTestId("retry-btn"));
    expect(mockRefetch).toHaveBeenCalled();
  });
});
