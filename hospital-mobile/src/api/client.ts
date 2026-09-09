import axios, { AxiosError } from "axios";
import { envConfig } from "../config/env";
import { tokenStorage } from "../storage/tokenStorage";
import { triggerSessionExpiry } from "../auth/sessionExpiryCoordinator";

export const apiClient = axios.create({
  baseURL: envConfig.apiUrl,
  timeout: envConfig.timeoutMs,
  headers: {
    "Accept": "application/json",
    "Content-Type": "application/json",
  },
});

apiClient.interceptors.request.use(async (config) => {
  const isPublic = (config as any).isPublic === true;
  if (!isPublic) {
    const token = await tokenStorage.getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
  }
  return config;
}, (error) => Promise.reject(error));

apiClient.interceptors.response.use(
  (response) => response,
  async (error: AxiosError) => {
    const isPublic = (error.config as any)?.isPublic === true;
    const status = error.response?.status;

    if (isPublic) {
      return Promise.reject(error);
    }

    if (status === 401) {
      await triggerSessionExpiry();
    }

    return Promise.reject(error);
  }
);