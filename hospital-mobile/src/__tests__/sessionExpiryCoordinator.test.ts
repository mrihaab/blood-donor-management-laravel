import {
  triggerSessionExpiry,
  registerSessionExpiryHandler,
  notifySessionStarted,
  resetSessionExpiryCoordinatorForTests,
} from "../auth/sessionExpiryCoordinator";
import { tokenStorage } from "../storage/tokenStorage";
import { queryClient } from "../api/queryClient";

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    clearToken: jest.fn().mockResolvedValue(true),
    getToken: jest.fn().mockResolvedValue(null),
    setToken: jest.fn().mockResolvedValue(true),
  },
}));

jest.mock("../api/queryClient", () => ({
  queryClient: {
    clear: jest.fn(),
  },
}));

describe("sessionExpiryCoordinator Unit Tests", () => {
  beforeEach(() => {
    resetSessionExpiryCoordinatorForTests();
    jest.clearAllMocks();
  });

  test("1. Five concurrent 401 calls share one in-flight promise and perform ONE cleanup", async () => {
    const mockHandler = jest.fn().mockResolvedValue(undefined);
    registerSessionExpiryHandler(mockHandler);

    const promises = Array.from({ length: 5 }, () => triggerSessionExpiry());

    // Verify all 5 calls return the EXACT SAME promise reference
    expect(promises[0]).toBe(promises[1]);
    expect(promises[1]).toBe(promises[2]);
    expect(promises[2]).toBe(promises[3]);
    expect(promises[3]).toBe(promises[4]);

    await Promise.all(promises);

    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(1);
    expect(queryClient.clear).toHaveBeenCalledTimes(1);
    expect(mockHandler).toHaveBeenCalledTimes(1);
  });

  test("2. Late 401 for the expired session does not repeat cleanup", async () => {
    const mockHandler = jest.fn().mockResolvedValue(undefined);
    registerSessionExpiryHandler(mockHandler);

    await triggerSessionExpiry();
    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(1);

    // Late 401 arrives for same session
    await triggerSessionExpiry();
    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(1);
    expect(mockHandler).toHaveBeenCalledTimes(1);
  });

  test("3. A new session can later be expired after notifySessionStarted", async () => {
    const mockHandler = jest.fn().mockResolvedValue(undefined);
    registerSessionExpiryHandler(mockHandler);

    await triggerSessionExpiry();
    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(1);

    notifySessionStarted("new_session_token");

    await triggerSessionExpiry();
    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(2);
    expect(mockHandler).toHaveBeenCalledTimes(2);
  });

  test("4. Unregister function removes stale handler", async () => {
    const mockHandler = jest.fn().mockResolvedValue(undefined);
    const unregister = registerSessionExpiryHandler(mockHandler);

    unregister();

    await triggerSessionExpiry();
    expect(mockHandler).not.toHaveBeenCalled();
    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(1);
  });
});
