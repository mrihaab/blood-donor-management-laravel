import {
  loginApi,
  getMeApi,
  logoutApi,
  logoutAllApi,
  getDeviceName,
  getSafeErrorMessage,
} from "../api/authApi";
import { publicApiClient, authenticatedApiClient } from "../api/client";

jest.mock("../api/client", () => ({
  publicApiClient: {
    post: jest.fn(),
  },
  authenticatedApiClient: {
    get: jest.fn(),
    post: jest.fn(),
  },
}));

describe("authApi Unit Tests", () => {
  beforeEach(() => {
    jest.resetAllMocks();
  });

  test("1. loginApi sends correct endpoint and payload", async () => {
    const mockResponse = {
      data: {
        message: "Login successful",
        token: "sample_token_123",
        user: {
          id: 1,
          name: "Dr. Jane Doe",
          email: "jane@hospital.org",
          role: "hospital_staff",
          hospital: {
            id: 10,
            name: "City General Hospital",
            license_number: "LIC-12345",
            status: "active",
          },
        },
      },
    };
    (publicApiClient.post as jest.Mock).mockResolvedValue(mockResponse);

    const result = await loginApi("jane@hospital.org", "SecretPassword123!", "android-test-device");

    expect(publicApiClient.post).toHaveBeenCalledWith("/auth/login", {
      email: "jane@hospital.org",
      password: "SecretPassword123!",
      device_name: "android-test-device",
    });
    expect(result).toEqual(mockResponse.data);
  });

  test("2. getMeApi calls /auth/me via authenticated client", async () => {
    const mockResponse = {
      data: {
        user: {
          id: 1,
          name: "Dr. Jane Doe",
          email: "jane@hospital.org",
          role: "hospital_staff",
          hospital: {
            id: 10,
            name: "City General Hospital",
            license_number: "LIC-12345",
            status: "active",
          },
        },
      },
    };
    (authenticatedApiClient.get as jest.Mock).mockResolvedValue(mockResponse);

    const result = await getMeApi();

    expect(authenticatedApiClient.get).toHaveBeenCalledWith("/auth/me");
    expect(result).toEqual(mockResponse.data);
  });

  test("3. logoutApi calls /auth/logout via authenticated client", async () => {
    const mockResponse = { data: { message: "Logged out successfully" } };
    (authenticatedApiClient.post as jest.Mock).mockResolvedValue(mockResponse);

    const result = await logoutApi();

    expect(authenticatedApiClient.post).toHaveBeenCalledWith("/auth/logout");
    expect(result).toEqual(mockResponse.data);
  });

  test("4. logoutAllApi calls /auth/logout-all via authenticated client", async () => {
    const mockResponse = { data: { message: "Tokens revoked" } };
    (authenticatedApiClient.post as jest.Mock).mockResolvedValue(mockResponse);

    const result = await logoutAllApi();

    expect(authenticatedApiClient.post).toHaveBeenCalledWith("/auth/logout-all");
    expect(result).toEqual(mockResponse.data);
  });

  test("5. getDeviceName returns trimmed non-empty string under 255 chars", () => {
    const deviceName = getDeviceName();
    expect(typeof deviceName).toBe("string");
    expect(deviceName.length).toBeGreaterThan(0);
    expect(deviceName.length).toBeLessThanOrEqual(255);
  });

  describe("getSafeErrorMessage Error Mapping", () => {
    test("maps 401 during login to 'Invalid email or password.'", () => {
      const err = {
        isAxiosError: true,
        response: { status: 401, data: { message: "Unauthenticated." } },
      };
      expect(getSafeErrorMessage(err, "login")).toBe("Invalid email or password.");
    });

    test("maps 401 during general context to session expired", () => {
      const err = {
        isAxiosError: true,
        response: { status: 401, data: { message: "Unauthenticated." } },
      };
      expect(getSafeErrorMessage(err, "general")).toBe("Your session has expired. Please log in again.");
    });

    test("maps 403 to 'This account cannot access the hospital mobile app.'", () => {
      const err = {
        isAxiosError: true,
        response: { status: 403, data: { message: "Forbidden." } },
      };
      expect(getSafeErrorMessage(err)).toBe("This account cannot access the hospital mobile app.");
    });

    test("maps 422 with validation errors to first error message", () => {
      const err = {
        isAxiosError: true,
        response: {
          status: 422,
          data: {
            message: "The given data was invalid.",
            errors: { email: ["The email field is required."] },
          },
        },
      };
      expect(getSafeErrorMessage(err)).toBe("The email field is required.");
    });

    test("maps 429 to 'Too many login attempts. Please try again shortly.'", () => {
      const err = {
        isAxiosError: true,
        response: { status: 429, data: { message: "Too Many Requests" } },
      };
      expect(getSafeErrorMessage(err)).toBe("Too many login attempts. Please try again shortly.");
    });

    test("maps network / timeout (no response) to connection error message", () => {
      const err = {
        isAxiosError: true,
        response: undefined,
      };
      expect(getSafeErrorMessage(err)).toBe(
        "Unable to connect to the hospital server. Check your connection and try again."
      );
    });

    test("maps 500 server error to temporarily unavailable message", () => {
      const err = {
        isAxiosError: true,
        response: { status: 500, data: { message: "Internal Server Error" } },
      };
      expect(getSafeErrorMessage(err)).toBe("The hospital server is temporarily unavailable. Please try again.");
    });

    test("sanitizes SQL / stack trace messages", () => {
      const err = {
        isAxiosError: true,
        response: {
          status: 422,
          data: {
            message: "SQLSTATE[23000]: Integrity constraint violation in vendor/laravel/framework...",
          },
        },
      };
      expect(getSafeErrorMessage(err)).toBe("The hospital server encountered an internal error. Please try again.");
    });
  });
});
