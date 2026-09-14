import { getDashboardApi } from "../api/dashboardApi";
import { authenticatedApiClient } from "../api/client";

jest.mock("../api/client", () => ({
  authenticatedApiClient: {
    get: jest.fn(),
  },
}));

describe("dashboardApi Unit Tests", () => {
  beforeEach(() => {
    jest.resetAllMocks();
  });

  test("getDashboardApi calls GET /dashboard and returns data payload", async () => {
    const mockData = {
      hospital: {
        id: 1,
        name: "Central Hospital",
        license_number: "LIC-100",
        city: "Dhaka",
        status: "active",
      },
      kpis: {
        total_patients: 15,
        total_requisitions: 25,
        pending_requisitions: 5,
        approved_requisitions: 12,
        dispensed_requisitions: 8,
      },
      recent_requisitions: [
        {
          id: 101,
          patient_name: "John Doe",
          blood_group: "A+",
          units_needed: 2,
          urgency_level: "urgent",
          status: "pending",
          created_at: "2026-09-14 10:00:00",
        },
      ],
    };

    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: {
        data: mockData,
      },
    });

    const result = await getDashboardApi();

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/dashboard");
    expect(result).toEqual(mockData);
  });
});
