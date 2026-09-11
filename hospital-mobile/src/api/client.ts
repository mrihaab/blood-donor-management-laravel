import axios, { AxiosError } from "axios";
import { envConfig } from "../config/env";
import { tokenStorage } from "../storage/tokenStorage";
import { triggerSessionExpiry, getActiveSessionId } from "../auth/sessionExpiryCoordinator";

// Public API client: no Authorization header attached, no session expiry on 401
export const publicApiClient = axios.create({
  baseURL: envConfig.apiUrl,
  timeout: envConfig.timeoutMs,
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json",
  },
});

// Authenticated API client: attaches Bearer token, triggers session expiry on HTTP 401
export const authenticatedApiClient = axios.create({
  baseURL: envConfig.apiUrl,
  timeout: envConfig.timeoutMs,
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json",
  },
});

authenticatedApiClient.interceptors.request.use(
  async (config) => {
    const token = await tokenStorage.getToken();
    if (token) {
      config.headers = config.headers || {};
      config.headers.Authorization = `Bearer ${token}`;
      (config as any)._sessionId = getActiveSessionId();
    }
    return config;
  },
  (error) => Promise.reject(error)
);

authenticatedApiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const status = error.response?.status;
    if (status === 401) {
      const requestSessionId = (error.config as any)?._sessionId;
      await triggerSessionExpiry(requestSessionId);
    }
    return Promise.reject(error);
  }
);

export const apiClient = authenticatedApiClient;