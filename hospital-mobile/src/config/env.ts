export interface EnvConfig {
  environment: "development" | "staging" | "production";
  apiUrl: string;
  isHttpsOnly: boolean;
  timeoutMs: number;
}

const getEnvConfig = (): EnvConfig => {
  const env = ((process.env.EXPO_PUBLIC_ENV || "development") as string).trim() as EnvConfig["environment"];
  const rawApiUrl = process.env.EXPO_PUBLIC_API_URL;

  let apiUrl = rawApiUrl ? rawApiUrl.trim() : "";

  if (!apiUrl) {
    if (env === "production") {
      throw new Error("FATAL: EXPO_PUBLIC_API_URL is required in production environment.");
    }
    // Default development fallback for Android emulator.
    // Note:
    // - emulator can use http://10.0.2.2:8000/api/v1/hospital;
    // - physical Android development can use http://127.0.0.1:8000/api/v1/hospital with `adb reverse tcp:8000 tcp:8000`, or a laptop LAN IP;
    // - production must use HTTPS.
    apiUrl = "http://10.0.2.2:8000/api/v1/hospital";
  }

  // Remove trailing slashes consistently
  apiUrl = apiUrl.replace(/\/+$/, "");

  // Fail with clear developer-safe configuration message if URL is malformed
  try {
    const parsed = new URL(apiUrl);
    if (!["http:", "https:"].includes(parsed.protocol)) {
      throw new Error("Invalid protocol");
    }
  } catch {
    throw new Error(`FATAL: Malformed EXPO_PUBLIC_API_URL configuration: ${apiUrl}`);
  }

  if (env !== "development" && !apiUrl.startsWith("https://")) {
    throw new Error(`FATAL: Insecure HTTP API URL rejected in ${env} environment: ${apiUrl}`);
  }

  return {
    environment: env,
    apiUrl,
    isHttpsOnly: apiUrl.startsWith("https://"),
    timeoutMs: 15000,
  };
};

export const envConfig = getEnvConfig();