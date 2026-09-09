import React, { createContext, useContext, useState, useEffect, useCallback } from "react";
import { tokenStorage } from "../storage/tokenStorage";
import { registerSessionExpiryHandler, notifySessionStarted } from "./sessionExpiryCoordinator";

export type AuthState = "bootstrapping" | "authenticated" | "unauthenticated";

export interface SessionValidator {
  validateToken: (token: string) => Promise<boolean>;
}

export const defaultSessionValidator: SessionValidator = {
  validateToken: async (_token: string) => {
    return false;
  },
};

const VALIDATION_TIMEOUT_MS = 5000;

function validateTokenWithTimeout(
  validator: SessionValidator,
  token: string,
  timeoutMs = VALIDATION_TIMEOUT_MS
): Promise<boolean> {
  return new Promise((resolve) => {
    let timer: any = null;
    let finished = false;

    timer = setTimeout(() => {
      if (!finished) {
        finished = true;
        resolve(false);
      }
    }, timeoutMs);

    validator
      .validateToken(token)
      .then((res) => {
        if (!finished) {
          finished = true;
          if (timer) clearTimeout(timer);
          resolve(res);
        }
      })
      .catch(() => {
        if (!finished) {
          finished = true;
          if (timer) clearTimeout(timer);
          resolve(false);
        }
      });
  });
}

interface AuthContextType {
  authState: AuthState;
  token: string | null;
  setAuthenticated: (token: string) => Promise<void>;
  setUnauthenticated: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{
  children: React.ReactNode;
  sessionValidator?: SessionValidator;
}> = ({ children, sessionValidator = defaultSessionValidator }) => {
  const [authState, setAuthState] = useState<AuthState>("bootstrapping");
  const [token, setToken] = useState<string | null>(null);

  const setUnauthenticated = useCallback(async () => {
    try {
      await tokenStorage.clearToken();
    } catch (e) {
      // In-memory auth state clears even if SecureStore deletion fails
    } finally {
      setToken(null);
      setAuthState("unauthenticated");
    }
  }, []);

  const setAuthenticated = useCallback(async (newToken: string) => {
    const success = await tokenStorage.setToken(newToken);
    if (!success) {
      setToken(null);
      setAuthState("unauthenticated");
      throw new Error("SecureStore persistence failed");
    }
    setToken(newToken);
    setAuthState("authenticated");
    notifySessionStarted(newToken);
  }, []);

  useEffect(() => {
    let isMounted = true;

    const unregister = registerSessionExpiryHandler(async () => {
      await setUnauthenticated();
    });

    const bootstrap = async () => {
      try {
        const storedToken = await tokenStorage.getToken();
        if (!storedToken) {
          if (isMounted) {
            setToken(null);
            setAuthState("unauthenticated");
          }
          return;
        }

        const isValid = await validateTokenWithTimeout(sessionValidator, storedToken);
        if (isValid) {
          if (isMounted) {
            setToken(storedToken);
            setAuthState("authenticated");
            notifySessionStarted(storedToken);
          }
        } else {
          await tokenStorage.clearToken().catch(() => {});
          if (isMounted) {
            setToken(null);
            setAuthState("unauthenticated");
          }
        }
      } catch (error) {
        await tokenStorage.clearToken().catch(() => {});
        if (isMounted) {
          setToken(null);
          setAuthState("unauthenticated");
        }
      }
    };

    bootstrap();

    return () => {
      isMounted = false;
      unregister();
    };
  }, [sessionValidator, setUnauthenticated]);

  return (
    <AuthContext.Provider value={{ authState, token, setAuthenticated, setUnauthenticated }}>
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