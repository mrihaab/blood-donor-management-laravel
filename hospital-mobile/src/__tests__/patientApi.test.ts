import {
  fetchPatients,
  fetchPatientById,
  createPatient,
  updatePatient,
  fetchBloodGroups,
} from "../api/patientApi";
import { authenticatedApiClient } from "../api/client";
import { CreatePatientPayload, UpdatePatientPayload } from "../types/patient";

jest.mock("../api/client", () => ({
  authenticatedApiClient: {
    get: jest.fn(),
    post: jest.fn(),
    put: jest.fn(),
  },
}));

describe("patientApi Unit Tests", () => {
  beforeEach(() => {
    jest.resetAllMocks();
  });

  const validPatientDetail = {
    id: 10,
    mrn: "MRN-100",
    name: "John Doe",
    gender: "male",
    date_of_birth: "1990-01-01",
    contact_number: "+1234567890",
    status: "active",
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

  const validPatientListMeta = {
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 1,
  };

  test("1. List calls exactly /patients", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: {
        data: [
          {
            id: 10,
            mrn: "MRN-100",
            name: "John Doe",
            gender: "male",
            status: "active",
            ward_name: "Ward A",
            room_number: "101",
            bed_number: "1",
            blood_group: { id: 1, name: "A+" },
            created_at: "2026-09-14 10:00:00",
          },
        ],
        meta: validPatientListMeta,
      },
    });

    await fetchPatients();

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/patients", { params: {} });
  });

  test("2. Detail calls exactly /patients/{id}", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    await fetchPatientById(10);

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/patients/10");
  });

  test("3. Store calls exactly /patients", async () => {
    (authenticatedApiClient.post as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const payload: CreatePatientPayload = {
      name: "Jane Doe",
      mrn: "MRN-101",
      gender: "female",
      date_of_birth: "1992-05-15",
    };

    await createPatient(payload);

    expect(authenticatedApiClient.post).toHaveBeenCalledWith("/patients", expect.any(Object));
  });

  test("4. Update calls exactly /patients/{id}", async () => {
    (authenticatedApiClient.put as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const payload: UpdatePatientPayload = {
      name: "John Updated",
    };

    await updatePatient(10, payload);

    expect(authenticatedApiClient.put).toHaveBeenCalledWith("/patients/10", expect.any(Object));
  });

  test("5. Blood groups call exactly /blood-groups", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [{ id: 1, name: "A+" }] },
    });

    await fetchBloodGroups();

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/blood-groups");
  });

  test("6. /api/v1/hospital is not duplicated in relative paths", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [], meta: validPatientListMeta },
    });

    await fetchPatients();

    const callArg = (authenticatedApiClient.get as jest.Mock).mock.calls[0][0];
    expect(callArg).not.toContain("/api/v1/hospital");
  });

  test("7. Search is trimmed", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [], meta: validPatientListMeta },
    });

    await fetchPatients({ search: "  John Doe  " });

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/patients", {
      params: { search: "John Doe" },
    });
  });

  test("8. Empty search is omitted", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [], meta: validPatientListMeta },
    });

    await fetchPatients({ search: "   " });

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/patients", { params: {} });
  });

  test("9. Undefined parameters are omitted", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [], meta: validPatientListMeta },
    });

    await fetchPatients({ page: 2, search: undefined, status: undefined });

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/patients", {
      params: { page: 2 },
    });
  });

  test("10. Only approved list parameters are sent", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [], meta: validPatientListMeta },
    });

    await fetchPatients({
      search: "John",
      status: "active",
      page: 1,
      per_page: 20,
    });

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/patients", {
      params: {
        search: "John",
        status: "active",
        page: 1,
        per_page: 20,
      },
    });
  });

  test("11. Create sends exact allowlisted fields", async () => {
    (authenticatedApiClient.post as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const payload: CreatePatientPayload = {
      name: "Jane Doe",
      mrn: "MRN-101",
      gender: "female",
      date_of_birth: "1992-05-15",
      blood_group_id: 2,
      contact_number: "+1987654321",
      ward_name: "Ward B",
      room_number: "202",
      bed_number: "5",
    };

    await createPatient(payload);

    expect(authenticatedApiClient.post).toHaveBeenCalledWith("/patients", {
      name: "Jane Doe",
      mrn: "MRN-101",
      gender: "female",
      date_of_birth: "1992-05-15",
      blood_group_id: 2,
      contact_number: "+1987654321",
      ward_name: "Ward B",
      room_number: "202",
      bed_number: "5",
    });
  });

  test("12. Create does not send status/identity fields", async () => {
    (authenticatedApiClient.post as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const payloadWithIdentity = {
      name: "Jane Doe",
      mrn: "MRN-101",
      gender: "female" as const,
      date_of_birth: "1992-05-15",
      id: 999,
      hospital_id: 888,
      user_id: 777,
      status: "active" as const,
      created_at: "2026-09-14",
      updated_at: "2026-09-14",
    };

    await createPatient(payloadWithIdentity as unknown as CreatePatientPayload);

    const postBody = (authenticatedApiClient.post as jest.Mock).mock.calls[0][1];
    expect(postBody).not.toHaveProperty("id");
    expect(postBody).not.toHaveProperty("hospital_id");
    expect(postBody).not.toHaveProperty("user_id");
    expect(postBody).not.toHaveProperty("status");
    expect(postBody).not.toHaveProperty("created_at");
    expect(postBody).not.toHaveProperty("updated_at");
  });

  test("13. Update sends exact allowlisted fields", async () => {
    (authenticatedApiClient.put as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const payload: UpdatePatientPayload = {
      name: "John Updated",
      gender: "male",
      date_of_birth: "1990-01-01",
      blood_group_id: 1,
      contact_number: "+1234567890",
      ward_name: "Ward A",
      room_number: "102",
      bed_number: "3",
    };

    await updatePatient(10, payload);

    expect(authenticatedApiClient.put).toHaveBeenCalledWith("/patients/10", {
      name: "John Updated",
      gender: "male",
      date_of_birth: "1990-01-01",
      blood_group_id: 1,
      contact_number: "+1234567890",
      ward_name: "Ward A",
      room_number: "102",
      bed_number: "3",
    });
  });

  test("14. Update does not send MRN/status/identity fields", async () => {
    (authenticatedApiClient.put as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const payloadWithForbiddenFields = {
      name: "John Updated",
      mrn: "MRN-FORBIDDEN",
      id: 999,
      hospital_id: 888,
      user_id: 777,
      status: "discharged" as const,
      created_at: "2026-09-14",
      updated_at: "2026-09-14",
    };

    await updatePatient(10, payloadWithForbiddenFields as unknown as UpdatePatientPayload);

    const putBody = (authenticatedApiClient.put as jest.Mock).mock.calls[0][1];
    expect(putBody).not.toHaveProperty("id");
    expect(putBody).not.toHaveProperty("mrn");
    expect(putBody).not.toHaveProperty("hospital_id");
    expect(putBody).not.toHaveProperty("user_id");
    expect(putBody).not.toHaveProperty("status");
    expect(putBody).not.toHaveProperty("created_at");
    expect(putBody).not.toHaveProperty("updated_at");
  });

  test("15. Valid list response parses", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: {
        data: [
          {
            id: 10,
            mrn: "MRN-100",
            name: "John Doe",
            gender: "male",
            status: "active",
            ward_name: "Ward A",
            room_number: "101",
            bed_number: "1",
            blood_group: { id: 1, name: "A+" },
            created_at: "2026-09-14 10:00:00",
          },
        ],
        meta: validPatientListMeta,
      },
    });

    const result = await fetchPatients();
    expect(result.data).toHaveLength(1);
    expect(result.data[0].name).toBe("John Doe");
    expect(result.meta.total).toBe(1);
  });

  test("16. Valid detail response parses", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const result = await fetchPatientById(10);
    expect(result.id).toBe(10);
    expect(result.mrn).toBe("MRN-100");
  });

  test("17. Valid null blood group parses", async () => {
    const detailWithoutBloodGroup = {
      ...validPatientDetail,
      blood_group: null,
    };
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: detailWithoutBloodGroup },
    });

    const result = await fetchPatientById(10);
    expect(result.blood_group).toBeNull();
  });

  test("18. Valid requisition summaries parse", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    const result = await fetchPatientById(10);
    expect(result.blood_requests).toHaveLength(1);
    expect(result.blood_requests![0].blood_group).toBe("A+");
  });

  test("19. Valid blood-group response parses", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: {
        data: [
          { id: 1, name: "A+" },
          { id: 2, name: "A-" },
        ],
      },
    });

    const result = await fetchBloodGroups();
    expect(result).toHaveLength(2);
    expect(result[0].name).toBe("A+");
  });

  test("20. Malformed top-level response throws TypeError", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: "not an object",
    });

    await expect(fetchPatients()).rejects.toThrow(TypeError);
  });

  test("21. Malformed pagination throws TypeError", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: {
        data: [],
        meta: { current_page: "invalid" },
      },
    });

    await expect(fetchPatients()).rejects.toThrow(TypeError);
  });

  test("22. Invalid Patient ID throws before Axios", async () => {
    await expect(fetchPatientById(0)).rejects.toThrow(TypeError);
    await expect(fetchPatientById(-5)).rejects.toThrow(TypeError);
    await expect(fetchPatientById(1.5)).rejects.toThrow(TypeError);
    await expect(fetchPatientById(NaN)).rejects.toThrow(TypeError);

    expect(authenticatedApiClient.get).not.toHaveBeenCalled();
  });

  test("23. Malformed Patient object throws TypeError", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: { id: "not-a-number", mrn: "MRN-100" } },
    });

    await expect(fetchPatientById(10)).rejects.toThrow(TypeError);
  });

  test("24. Invalid gender throws TypeError", async () => {
    const invalidGenderDetail = {
      ...validPatientDetail,
      gender: "unknown_gender",
    };
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: invalidGenderDetail },
    });

    await expect(fetchPatientById(10)).rejects.toThrow(TypeError);
  });

  test("25. Invalid status throws TypeError", async () => {
    const invalidStatusDetail = {
      ...validPatientDetail,
      status: "unknown_status",
    };
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: invalidStatusDetail },
    });

    await expect(fetchPatientById(10)).rejects.toThrow(TypeError);
  });

  test("26. Malformed blood-group object throws TypeError", async () => {
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: [{ id: "invalid", name: 123 }] },
    });

    await expect(fetchBloodGroups()).rejects.toThrow(TypeError);
  });

  test("27. Malformed requisition array throws TypeError", async () => {
    const malformedReqDetail = {
      ...validPatientDetail,
      blood_requests: [{ id: "invalid" }],
    };
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: malformedReqDetail },
    });

    await expect(fetchPatientById(10)).rejects.toThrow(TypeError);
  });

  test("28. Raw response/token data is not logged", async () => {
    const consoleSpy = jest.spyOn(console, "log").mockImplementation(() => {});
    const consoleErrSpy = jest.spyOn(console, "error").mockImplementation(() => {});

    (authenticatedApiClient.get as jest.Mock).mockResolvedValue({
      data: { data: validPatientDetail },
    });

    await fetchPatientById(10);

    expect(consoleSpy).not.toHaveBeenCalled();
    expect(consoleErrSpy).not.toHaveBeenCalled();

    consoleSpy.mockRestore();
    consoleErrSpy.mockRestore();
  });
});
