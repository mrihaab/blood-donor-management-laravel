import React from "react";
import { render, waitFor, fireEvent } from "@testing-library/react-native";
import { AppNavigator } from "../navigation/AppNavigator";
import { AuthProvider, SessionValidator } from "../auth/AuthContext";
import { tokenStorage } from "../storage/tokenStorage";

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn().mockResolvedValue(true),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

describe("AppNavigator Conditional Navigation Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();
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
      expect(screen.getByText("Clinical Operations Portal")).toBeTruthy();
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
    expect(screen.queryByText("Clinical Operations Portal")).toBeNull();
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

    fireEvent.press(screen.getByText("Logout This Device"));

    await waitFor(() => {
      expect(screen.getByText("Clinical Operations Portal")).toBeTruthy();
    });

    // Verify Dashboard is completely unmounted and not present in tree
    expect(screen.queryByText("Hospital Dashboard")).toBeNull();
  });
});
