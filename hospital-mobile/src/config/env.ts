export interface EnvConfig {
  environment: "development" | "staging" | "production";
  apiUrl: string;
  isHttpsOnly: boolean;
  timeoutMs: number;
}

const getEnvConfig = (): EnvConfig => {
  const env = (process.env.EXPO_PUBLIC_ENV || "development") as EnvConfig["environment"];
  const rawApiUrl = process.env.EXPO_PUBLIC_API_URL;

  let apiUrl = rawApiUrl;

  if (!apiUrl) {
    if (env === "production") {
      throw new Error("FATAL: EXPO_PUBLIC_API_URL is required in production environment.");
    }
    apiUrl = "http://10.0.2.2:8000/api/v1/hospital";
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