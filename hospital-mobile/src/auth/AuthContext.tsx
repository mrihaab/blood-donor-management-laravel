import React, { createContext, useContext, useState, useEffect } from "react";
import { tokenStorage } from "../storage/tokenStorage";
import { registerSessionExpiryHandler } from "./sessionExpiryCoordinator";

export type AuthState = "bootstrapping" | "provisional_restoration" | "authenticated" | "unauthenticated";

export interface SessionValidator {
  validateToken: (token: string) => Promise<boolean>;
}

export const defaultSessionValidator: SessionValidator = {
  validateToken: async (_token: string) => {
    return false;
  },
};

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

  const setUnauthenticated = async () => {
    try {
      await tokenStorage.clearToken();
    } catch (e) {
      console.warn("Storage clear failure ignored during logout");
    }
    setToken(null);
    setAuthState("unauthenticated");
  };

  const setAuthenticated = async (newToken: string) => {
    const success = await tokenStorage.setToken(newToken);
    if (!success) {
      setToken(null);
      setAuthState("unauthenticated");
      throw new Error("SecureStore persistence failed");
    }
    setToken(newToken);
    setAuthState("authenticated");
  };

  useEffect(() => {
    registerSessionExpiryHandler(setUnauthenticated);

    const bootstrap = async () => {
      try {
        const storedToken = await tokenStorage.getToken();
        if (storedToken) {
          setToken(storedToken);
          const isValid = await sessionValidator.validateToken(storedToken);
          if (isValid) {
            setAuthState("authenticated");
          } else {
            setAuthState("provisional_restoration");
          }
        } else {
          setAuthState("unauthenticated");
        }
      } catch (error) {
        setToken(null);
        setAuthState("unauthenticated");
      }
    };

    bootstrap();
  }, []);

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