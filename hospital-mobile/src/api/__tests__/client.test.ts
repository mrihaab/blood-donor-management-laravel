import { tokenStorage } from "../../storage/tokenStorage";
import { queryClient } from "../queryClient";
import { resetToLogin } from "../../navigation/navigationRef";

jest.mock("../../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn(),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

jest.mock("../queryClient", () => ({
  queryClient: {
    clear: jest.fn(),
  },
}));

jest.mock("../../navigation/navigationRef", () => ({
  resetToLogin: jest.fn(),
}));

describe("Axios 401 Session Expiry Single-Flight Handler", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("triggers clearToken, queryClient.clear, and resetToLogin once on single-flight 401", async () => {
    let isHandlingExpiry = false;

    const handleSingleFlight401 = async (status: number, isPublic: boolean) => {
      if (isPublic) return;
      if (status === 401 && !isHandlingExpiry) {
        isHandlingExpiry = true;
        try {
          await tokenStorage.clearToken();
          queryClient.clear();
          resetToLogin();
        } finally {
          setTimeout(() => {
            isHandlingExpiry = false;
          }, 100);
        }
      }
    };

    await Promise.all([
      handleSingleFlight401(401, false),
      handleSingleFlight401(401, false),
      handleSingleFlight401(401, false),
      handleSingleFlight401(401, false),
      handleSingleFlight401(401, false),
    ]);

    expect(tokenStorage.clearToken).toHaveBeenCalledTimes(1);
    expect(queryClient.clear).toHaveBeenCalledTimes(1);
    expect(resetToLogin).toHaveBeenCalledTimes(1);
  });

  it("does NOT clear token on 403, 404, 422, or 429 status codes", async () => {
    let isHandlingExpiry = false;

    const handleSingleFlight401 = async (status: number, isPublic: boolean) => {
      if (isPublic) return;
      if (status === 401 && !isHandlingExpiry) {
        isHandlingExpiry = true;
        try {
          await tokenStorage.clearToken();
          queryClient.clear();
          resetToLogin();
        } finally {
          setTimeout(() => {
            isHandlingExpiry = false;
          }, 100);
        }
      }
    };

    await handleSingleFlight401(403, false);
    await handleSingleFlight401(404, false);
    await handleSingleFlight401(422, false);
    await handleSingleFlight401(429, false);

    expect(tokenStorage.clearToken).not.toHaveBeenCalled();
    expect(queryClient.clear).not.toHaveBeenCalled();
    expect(resetToLogin).not.toHaveBeenCalled();
  });
});