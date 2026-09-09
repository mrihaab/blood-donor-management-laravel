import * as SecureStore from "expo-secure-store";
import { tokenStorage } from "../storage/tokenStorage";

jest.mock("expo-secure-store", () => ({
  getItemAsync: jest.fn(),
  setItemAsync: jest.fn(),
  deleteItemAsync: jest.fn(),
}));

describe("tokenStorage Unit Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test("1. SecureStore get/set/delete calls invoke SecureStore API correctly", async () => {
    (SecureStore.getItemAsync as jest.Mock).mockResolvedValue("test_bearer_token");
    (SecureStore.setItemAsync as jest.Mock).mockResolvedValue(undefined);
    (SecureStore.deleteItemAsync as jest.Mock).mockResolvedValue(undefined);

    const token = await tokenStorage.getToken();
    expect(token).toBe("test_bearer_token");
    expect(SecureStore.getItemAsync).toHaveBeenCalledWith("hospital_mobile_bearer_token");

    const setRes = await tokenStorage.setToken("new_token");
    expect(setRes).toBe(true);
    expect(SecureStore.setItemAsync).toHaveBeenCalledWith("hospital_mobile_bearer_token", "new_token");

    const clearRes = await tokenStorage.clearToken();
    expect(clearRes).toBe(true);
    expect(SecureStore.deleteItemAsync).toHaveBeenCalledWith("hospital_mobile_bearer_token");
  });

  test("2. SecureStore read failure returns null gracefully", async () => {
    (SecureStore.getItemAsync as jest.Mock).mockRejectedValue(new Error("Storage corrupted"));

    const token = await tokenStorage.getToken();
    expect(token).toBeNull();
  });

  test("3. SecureStore write failure returns false", async () => {
    (SecureStore.setItemAsync as jest.Mock).mockRejectedValue(new Error("Disk full"));

    const res = await tokenStorage.setToken("token");
    expect(res).toBe(false);
  });

  test("4. SecureStore delete failure returns false", async () => {
    (SecureStore.deleteItemAsync as jest.Mock).mockRejectedValue(new Error("Key not found"));

    const res = await tokenStorage.clearToken();
    expect(res).toBe(false);
  });
});
