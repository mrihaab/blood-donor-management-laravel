import * as SecureStore from "expo-secure-store";

const TOKEN_KEY = "hospital_mobile_bearer_token";

export const tokenStorage = {
  async getToken(): Promise<string | null> {
    try {
      return await SecureStore.getItemAsync(TOKEN_KEY);
    } catch (error) {
      return null;
    }
  },

  async setToken(token: string): Promise<boolean> {
    try {
      await SecureStore.setItemAsync(TOKEN_KEY, token);
      return true;
    } catch (error) {
      return false;
    }
  },

  async clearToken(): Promise<boolean> {
    try {
      await SecureStore.deleteItemAsync(TOKEN_KEY);
      return true;
    } catch (error) {
      return false;
    }
  }
};