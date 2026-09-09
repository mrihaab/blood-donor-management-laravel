import React, { createContext, useContext, useState, useEffect } from "react";
import { tokenStorage } from "../storage/tokenStorage";

export type AuthState = "bootstrapping" | "authenticated" | "unauthenticated";

interface AuthContextType {
  authState: AuthState;
  token: string | null;
  setAuthenticated: (token: string) => Promise<void>;
  setUnauthenticated: () => Promise<void>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [authState, setAuthState] = useState<AuthState>("bootstrapping");
  const [token, setToken] = useState<string | null>(null);

  useEffect(() => {
    const bootstrap = async () => {
      const storedToken = await tokenStorage.getToken();
      if (storedToken) {
        setToken(storedToken);
        setAuthState("authenticated");
      } else {
        setAuthState("unauthenticated");
      }
    };
    bootstrap();
  }, []);

  const setAuthenticated = async (newToken: string) => {
    await tokenStorage.setToken(newToken);
    setToken(newToken);
    setAuthState("authenticated");
  };

  const setUnauthenticated = async () => {
    await tokenStorage.clearToken();
    setToken(null);
    setAuthState("unauthenticated");
  };

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