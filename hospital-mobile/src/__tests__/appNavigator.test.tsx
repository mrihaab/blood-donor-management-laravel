import React from "react";
import { render, waitFor, fireEvent, act } from "@testing-library/react-native";
import { AppNavigator } from "../navigation/AppNavigator";
import { AuthProvider, SessionValidator } from "../auth/AuthContext";
import { tokenStorage } from "../storage/tokenStorage";
import { logoutApi } from "../api/authApi";

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn().mockResolvedValue(true),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

jest.mock("../api/authApi", () => {
  const original = jest.requireActual("../api/authApi");
  return {
    ...original,
    loginApi: jest.fn(),
    getMeApi: jest.fn(),
    logoutApi: jest.fn(),
  };
});

describe("AppNavigator Conditional Navigation Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();
    (tokenStorage.clearToken as jest.Mock).mockResolvedValue(true);
    (tokenStorage.setToken as jest.Mock).mockResolvedValue(true);
    (logoutApi as jest.Mock).mockResolvedValue({ message: "Logged out" });
  });

  test("1. Bootstrapping state displays SplashScreen with no protected Dashboard flash", async () => {
    (tokenStorage.getToken as jest.Mock).mockReturnValue(new Promise(() => {})); // pending promise

    const screen = await render(
      <AuthProvider>
        <AppNavigator />
      </AuthProvider>
    );

    expect(screen.getByText("Initializing Secure Session...")).toBeTruthy();
    expect(screen.queryByText("Hospital Dashboard")).toBeNull();
  });

  test("2. Unauthenticated state displays LoginScreen and no Dashboard", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);

    const screen = await render(
      <AuthProvider>
        <AppNavigator />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByText("Hospital Operations Portal")).toBeTruthy();
    });
    expect(screen.queryByText("Hospital Dashboard")).toBeNull();
  });

  test("3. Valid token validation displays DashboardScreen", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(true),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <AppNavigator />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByText("Hospital Dashboard")).toBeTruthy();
    });
    expect(screen.queryByText("Hospital Operations Portal")).toBeNull();
  });

  test("4. Logout removes Dashboard from navigation stack completely (Android Back protection)", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(true),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <AppNavigator />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByText("Hospital Dashboard")).toBeTruthy();
    });

    await act(async () => {
      fireEvent.press(screen.getByTestId("logout-button"));
    });

    await waitFor(() => {
      expect(screen.getByText("Hospital Operations Portal")).toBeTruthy();
    });

    // Verify Dashboard is completely unmounted and not present in tree
    expect(screen.queryByText("Hospital Dashboard")).toBeNull();
  });
});
