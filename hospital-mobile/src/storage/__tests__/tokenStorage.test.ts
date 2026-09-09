import { tokenStorage } from "../tokenStorage";
import * as SecureStore from "expo-secure-store";

jest.mock("expo-secure-store", () => ({
  getItemAsync: jest.fn(),
  setItemAsync: jest.fn(),
  deleteItemAsync: jest.fn(),
}));

describe("tokenStorage Interface", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("returns token from SecureStore", async () => {
    (SecureStore.getItemAsync as jest.Mock).mockResolvedValue("test_bearer_token");
    const token = await tokenStorage.getToken();
    expect(token).toBe("test_bearer_token");
    expect(SecureStore.getItemAsync).toHaveBeenCalledWith("hospital_mobile_bearer_token");
  });

  it("persists token to SecureStore", async () => {
    (SecureStore.setItemAsync as jest.Mock).mockResolvedValue(undefined);
    const result = await tokenStorage.setToken("test_bearer_token");
    expect(result).toBe(true);
    expect(SecureStore.setItemAsync).toHaveBeenCalledWith("hospital_mobile_bearer_token", "test_bearer_token");
  });

  it("clears token from SecureStore", async () => {
    (SecureStore.deleteItemAsync as jest.Mock).mockResolvedValue(undefined);
    const result = await tokenStorage.clearToken();
    expect(result).toBe(true);
    expect(SecureStore.deleteItemAsync).toHaveBeenCalledWith("hospital_mobile_bearer_token");
  });
});