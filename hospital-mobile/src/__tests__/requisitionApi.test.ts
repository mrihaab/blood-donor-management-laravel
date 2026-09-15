import {
  fetchRequisitions,
  fetchRequisitionById,
  createRequisition,
  parseRequisitionListItem,
  parseRequisitionDetail,
} from "../api/requisitionApi";
import { authenticatedApiClient } from "../api/client";
import { CreateRequisitionPayload } from "../types/requisition";

jest.mock("../api/client", () => ({
  authenticatedApiClient: {
    get: jest.fn(),
    post: jest.fn(),
    put: jest.fn(),
  },
}));

describe("requisitionApi Unit Tests", () => {
  beforeEach(() => {
    jest.resetAllMocks();
  });

  const validDetailPayload = {
    id: 15,
    patient_id: 10,
    patient_name: "Patient Alpha",
    blood_group: "A+",
    units_needed: 2,
    urgency_level: "urgent",
    status: "pending",
    required_by: "2026-09-20 12:00:00",
    reason: "Surgery planned",
    approved_at: null,
    rejected_at: null,
    created_at: "2026-09-15 08:00:00",
    updated_at: "2026-09-15 08:00:00",
    patient: {
      id: 10,
      mrn: "MRN-HOSP-A-001",
      name: "Patient Alpha",
    },
  };

  test("1. fetchRequisitions calls endpoint with query params", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: {
        data: [
          {
            id: 15,
            patient_id: 10,
            patient_name: "Patient Alpha",
            blood_group: "A+",
            units_needed: 2,
            urgency_level: "urgent",
            status: "pending",
            created_at: "2026-09-15 08:00:00",
            patient: { id: 10, mrn: "MRN-HOSP-A-001", name: "Patient Alpha" },
          },
        ],
        meta: { current_page: 1, last_page: 1, per_page: 15, total: 1 },
      },
    });

    const res = await fetchRequisitions({ status: "pending", page: 1 });

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/requisitions", {
      params: { status: "pending", page: 1 },
    });
    expect(res.data).toHaveLength(1);
    expect(res.data[0].id).toBe(15);
    expect(res.data[0].patient_name).toBe("Patient Alpha");
  });

  test("2. fetchRequisitionById calls endpoint and parses detail", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: validDetailPayload },
    });

    const detail = await fetchRequisitionById(15);

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/requisitions/15");
    expect(detail.id).toBe(15);
    expect(detail.urgency_level).toBe("urgent");
    expect(detail.reason).toBe("Surgery planned");
  });

  test("3. createRequisition calls POST endpoint with payload", async () => {
    (authenticatedApiClient.post as jest.Mock).mockResolvedValue({
      data: { data: validDetailPayload },
    });

    const payload: CreateRequisitionPayload = {
      patient_id: 10,
      blood_group: "A+",
      units_needed: 2,
      urgency_level: "urgent",
      reason: "Surgery planned",
    };

    const res = await createRequisition(payload);

    expect(authenticatedApiClient.post).toHaveBeenCalledWith("/requisitions", payload);
    expect(res.id).toBe(15);
  });

  test("4. Response parsers validate required allowlist attributes and throw on invalid shapes", () => {
    expect(() => parseRequisitionListItem({})).toThrow(TypeError);
    expect(() => parseRequisitionListItem({ id: "invalid" })).toThrow(TypeError);
    expect(() => parseRequisitionDetail(null)).toThrow(TypeError);

    const parsed = parseRequisitionListItem({
      id: 20,
      patient_name: "Jane",
      blood_group: "O-",
      units_needed: 1,
      urgency_level: "emergency",
      status: "approved",
      created_at: null,
    });

    expect(parsed.id).toBe(20);
    expect(parsed.blood_group).toBe("O-");
    expect(parsed.urgency_level).toBe("emergency");
  });
});
