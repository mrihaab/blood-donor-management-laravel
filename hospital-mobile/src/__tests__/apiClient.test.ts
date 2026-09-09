import { publicApiClient, authenticatedApiClient } from "../api/client";
import { tokenStorage } from "../storage/tokenStorage";
import * as sessionExpiryModule from "../auth/sessionExpiryCoordinator";

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn(),
    clearToken: jest.fn(),
  },
}));

jest.mock("../auth/sessionExpiryCoordinator", () => ({
  triggerSessionExpiry: jest.fn().mockResolvedValue(undefined),
}));

describe("apiClient Dual Instance Unit Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test("1. authenticatedApiClient attaches Bearer token header", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("secret_bearer_token");

    // Interceptor test
    const config: any = { headers: {} };
    const requestInterceptor = (authenticatedApiClient.interceptors.request as any).handlers[0].fulfilled;
    const resultConfig = await requestInterceptor(config);

    expect(resultConfig.headers.Authorization).toBe("Bearer secret_bearer_token");
  });

  test("2. authenticatedApiClient HTTP 401 triggers session expiry", async () => {
    const error: any = {
      response: { status: 401 },
    };
    const responseInterceptor = (authenticatedApiClient.interceptors.response as any).handlers[0].rejected;

    await expect(responseInterceptor(error)).rejects.toEqual(error);
    expect(sessionExpiryModule.triggerSessionExpiry).toHaveBeenCalledTimes(1);
  });

  test("3. publicApiClient HTTP 401 does NOT trigger session expiry", async () => {
    const error: any = {
      response: { status: 401 },
    };
    const responseInterceptor = (publicApiClient.interceptors.response as any).handlers[0]?.rejected;

    if (responseInterceptor) {
      await expect(responseInterceptor(error)).rejects.toEqual(error);
    }
    expect(sessionExpiryModule.triggerSessionExpiry).not.toHaveBeenCalled();
  });

  test("4. Non-401 errors (403, 404, 422, 429, network) do NOT trigger session expiry", async () => {
    const statusCodes = [403, 404, 422, 429, 500, undefined];
    const responseInterceptor = (authenticatedApiClient.interceptors.response as any).handlers[0].rejected;

    for (const status of statusCodes) {
      const error: any = { response: status ? { status } : undefined };
      await expect(responseInterceptor(error)).rejects.toEqual(error);
    }

    expect(sessionExpiryModule.triggerSessionExpiry).not.toHaveBeenCalled();
  });
});
