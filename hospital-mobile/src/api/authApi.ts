import axios from "axios";
import { Platform } from "react-native";
import { publicApiClient, authenticatedApiClient } from "./client";

export interface HospitalProfile {
  id: number;
  name: string;
  license_number: string;
  status: string;
}

export interface UserProfile {
  id: number;
  name: string;
  email: string;
  role: string;
  hospital: HospitalProfile | null;
}

export interface LoginRequestPayload {
  email: string;
  password: string;
  device_name: string;
}

export interface LoginResponsePayload {
  message: string;
  token: string;
  user: UserProfile;
}

export interface MeResponsePayload {
  user: UserProfile;
}

export interface ApiErrorPayload {
  message: string;
  errors?: Record<string, string[]>;
}

export function getDeviceName(): string {
  const os = Platform.OS || "mobile";
  const rawName = `${os}-hospital-mobile`;
  return rawName.trim().substring(0, 255);
}

export async function loginApi(
  email: string,
  password: string,
  deviceName: string
): Promise<LoginResponsePayload> {
  const payload: LoginRequestPayload = {
    email,
    password,
    device_name: deviceName,
  };
  const response = await publicApiClient.post<LoginResponsePayload>("/auth/login", payload);
  return response.data;
}

export async function getMeApi(): Promise<MeResponsePayload> {
  const response = await authenticatedApiClient.get<MeResponsePayload>("/auth/me");
  return response.data;
}

export async function logoutApi(): Promise<{ message: string }> {
  const response = await authenticatedApiClient.post<{ message: string }>("/auth/logout");
  return response.data;
}

export async function logoutAllApi(): Promise<{ message: string }> {
  const response = await authenticatedApiClient.post<{ message: string }>("/auth/logout-all");
  return response.data;
}

export function getSafeErrorMessage(
  error: unknown,
  context: "login" | "general" = "general"
): string {
  if (!axios.isAxiosError(error)) {
    if (error instanceof Error && error.message) {
      return sanitizeMessage(error.message);
    }
    return "An unexpected error occurred. Please try again.";
  }

  if (!error.response) {
    return "Unable to connect to the hospital server. Check your connection and try again.";
  }

  const status = error.response.status;
  const data = error.response.data as ApiErrorPayload | undefined;

  if (status === 401) {
    return context === "login"
      ? "Invalid email or password."
      : "Your session has expired. Please log in again.";
  }

  if (status === 403) {
    return "This account cannot access the hospital mobile app.";
  }

  if (status === 422) {
    if (data?.errors && typeof data.errors === "object") {
      const keys = Object.keys(data.errors);
      if (keys.length > 0 && Array.isArray(data.errors[keys[0]]) && data.errors[keys[0]].length > 0) {
        return sanitizeMessage(data.errors[keys[0]][0]);
      }
    }
    if (data?.message && typeof data.message === "string") {
      return sanitizeMessage(data.message);
    }
    return "Validation failed. Please check your input.";
  }

  if (status === 429) {
    return "Too many login attempts. Please try again shortly.";
  }

  if (status >= 500) {
    return "The hospital server is temporarily unavailable. Please try again.";
  }

  if (data?.message && typeof data.message === "string") {
    return sanitizeMessage(data.message);
  }

  return "An unexpected error occurred. Please try again.";
}

function sanitizeMessage(msg: string): string {
  if (
    msg.includes("SQLSTATE") ||
    msg.includes("vendor/") ||
    msg.includes("Stack trace:") ||
    msg.includes("<html") ||
    msg.includes("Bearer ")
  ) {
    return "The hospital server encountered an internal error. Please try again.";
  }
  return msg;
}
