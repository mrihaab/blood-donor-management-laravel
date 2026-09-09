import axios, { AxiosError } from "axios";
import { envConfig } from "../config/env";
import { tokenStorage } from "../storage/tokenStorage";
import { triggerSessionExpiry } from "../auth/sessionExpiryCoordinator";

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
      await triggerSessionExpiry();
    }
    return Promise.reject(error);
  }
);

export const apiClient = authenticatedApiClient;