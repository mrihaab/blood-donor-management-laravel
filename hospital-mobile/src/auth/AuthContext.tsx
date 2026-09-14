import React, { createContext, useContext, useState, useEffect, useCallback, useRef } from "react";
import { tokenStorage } from "../storage/tokenStorage";
import { registerSessionExpiryHandler, notifySessionStarted } from "./sessionExpiryCoordinator";
import {
  UserProfile,
  loginApi,
  getMeApi,
  logoutApi,
  logoutAllApi,
  getDeviceName,
} from "../api/authApi";
import { queryClient } from "../api/queryClient";
import axios from "axios";

export type AuthState = "bootstrapping" | "authenticated" | "unauthenticated";

export interface SessionValidator {
  validateToken: (token: string) => Promise<boolean>;
}

interface AuthContextType {
  authState: AuthState;
  token: string | null;
  user: UserProfile | null;
  isLoading: boolean;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
  logoutAll?: () => Promise<void>;
  setAuthenticated: (token: string) => Promise<void>;
  setUnauthenticated: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{
  children: React.ReactNode;
  sessionValidator?: SessionValidator;
  validationTimeoutMs?: number;
}> = ({ children, sessionValidator, validationTimeoutMs = 5000 }) => {
  const [authState, setAuthState] = useState<AuthState>("bootstrapping");
  const [token, setToken] = useState<string | null>(null);
  const [user, setUser] = useState<UserProfile | null>(null);

  const isLoggingIn = useRef(false);
  const isLoggingOut = useRef(false);

  const setUnauthenticated = useCallback(async () => {
    try {
      await tokenStorage.clearToken();
    } catch {
      // Ignore deletion failure when clearing memory state
    } finally {
      queryClient.clear();
      setToken(null);
      setUser(null);
      setAuthState("unauthenticated");
    }
  }, []);

  const setAuthenticated = useCallback(async (newToken: string) => {
    const success = await tokenStorage.setToken(newToken);
    if (!success) {
      queryClient.clear();
      setToken(null);
      setUser(null);
      setAuthState("unauthenticated");
      throw new Error("SecureStore persistence failed");
    }
    setToken(newToken);
    setAuthState("authenticated");
    notifySessionStarted(newToken);
  }, []);

  const login = useCallback(
    async (email: string, password: string): Promise<void> => {
      if (isLoggingIn.current) {
        return;
      }
      isLoggingIn.current = true;

      try {
        const deviceName = getDeviceName();
        const response = await loginApi(email, password, deviceName);

        const newToken = response.token;
        const newUser = response.user;

        const success = await tokenStorage.setToken(newToken);
        if (!success) {
          try {
            await logoutApi();
          } catch {
            // Best effort revocation
          }
          queryClient.clear();
          setToken(null);
          setUser(null);
          setAuthState("unauthenticated");
          throw new Error("SecureStore persistence failed");
        }

        setToken(newToken);
        setUser(newUser);
        setAuthState("authenticated");
        notifySessionStarted(newToken);
      } catch (err) {
        queryClient.clear();
        setToken(null);
        setUser(null);
        setAuthState("unauthenticated");
        throw err;
      } finally {
        isLoggingIn.current = false;
      }
    },
    []
  );

  const logout = useCallback(async (): Promise<void> => {
    if (isLoggingOut.current) {
      return;
    }
    isLoggingOut.current = true;

    try {
      try {
        await logoutApi();
      } catch (err) {
        if (axios.isAxiosError(err) && err.response?.status === 401) {
          // Token already revoked on server, proceed to clear local session
        } else {
          // Network failure or 5xx: preserve local session and rethrow
          throw err;
        }
      }

      await tokenStorage.clearToken().catch(() => {});
      queryClient.clear();
      setToken(null);
      setUser(null);
      setAuthState("unauthenticated");
    } finally {
      isLoggingOut.current = false;
    }
  }, []);

  const logoutAll = useCallback(async (): Promise<void> => {
    try {
      await logoutAllApi().catch(() => {});
    } finally {
      await tokenStorage.clearToken().catch(() => {});
      queryClient.clear();
      setToken(null);
      setUser(null);
      setAuthState("unauthenticated");
    }
  }, []);

  useEffect(() => {
    let isMounted = true;

    const unregister = registerSessionExpiryHandler(async () => {
      if (isMounted) {
        queryClient.clear();
        setToken(null);
        setUser(null);
        setAuthState("unauthenticated");
      }
    });

    const bootstrap = async () => {
      try {
        const storedToken = await tokenStorage.getToken();
        if (!storedToken) {
          if (isMounted) {
            setToken(null);
            setUser(null);
            setAuthState("unauthenticated");
          }
          return;
        }

        if (sessionValidator) {
          let finished = false;
          const timer = setTimeout(() => {
            if (!finished && isMounted) {
              finished = true;
              tokenStorage.clearToken().catch(() => {});
              setToken(null);
              setUser(null);
              setAuthState("unauthenticated");
            }
          }, validationTimeoutMs);

          try {
            const isValid = await sessionValidator.validateToken(storedToken);
            finished = true;
            clearTimeout(timer);
            if (!isMounted) return;

            if (isValid) {
              setToken(storedToken);
              setAuthState("authenticated");
              notifySessionStarted(storedToken);
            } else {
              await tokenStorage.clearToken().catch(() => {});
              setToken(null);
              setUser(null);
              setAuthState("unauthenticated");
            }
          } catch {
            finished = true;
            clearTimeout(timer);
            if (!isMounted) return;
            await tokenStorage.clearToken().catch(() => {});
            setToken(null);
            setUser(null);
            setAuthState("unauthenticated");
          }
          return;
        }

        try {
          const meRes = await getMeApi();
          if (!isMounted) return;

          setToken(storedToken);
          setUser(meRes.user);
          setAuthState("authenticated");
          notifySessionStarted(storedToken);
        } catch (meError) {
          if (!isMounted) return;

          if (axios.isAxiosError(meError) && meError.response?.status === 401) {
            await tokenStorage.clearToken().catch(() => {});
          }
          setToken(null);
          setUser(null);
          setAuthState("unauthenticated");
        }
      } catch {
        if (isMounted) {
          setToken(null);
          setUser(null);
          setAuthState("unauthenticated");
        }
      }
    };

    bootstrap();

    return () => {
      isMounted = false;
      unregister();
    };
  }, [sessionValidator, validationTimeoutMs]);

  return (
    <AuthContext.Provider
      value={{
        authState,
        token,
        user,
        isLoading: authState === "bootstrapping",
        isAuthenticated: authState === "authenticated",
        login,
        logout,
        logoutAll,
        setAuthenticated,
        setUnauthenticated,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextType => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
};