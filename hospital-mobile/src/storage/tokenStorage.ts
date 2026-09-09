import * as SecureStore from "expo-secure-store";

const TOKEN_KEY = "hospital_mobile_bearer_token";

export const tokenStorage = {
  async getToken(): Promise<string | null> {
    try {
      return await SecureStore.getItemAsync(TOKEN_KEY);
    } catch (error) {
      console.warn("SecureStore: Failed to retrieve token from encrypted storage.");
      return null;
    }
  },

  async setToken(token: string): Promise<boolean> {
    try {
      await SecureStore.setItemAsync(TOKEN_KEY, token);
      return true;
    } catch (error) {
      console.warn("SecureStore: Failed to persist token to encrypted storage.");
      return false;
    }
  },

  async clearToken(): Promise<boolean> {
    try {
      await SecureStore.deleteItemAsync(TOKEN_KEY);
      return true;
    } catch (error) {
      console.warn("SecureStore: Failed to clear token from encrypted storage.");
      return false;
    }
  }
};