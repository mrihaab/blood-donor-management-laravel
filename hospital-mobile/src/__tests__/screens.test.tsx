import React from "react";
import { render, fireEvent, waitFor, act } from "@testing-library/react-native";
import { LoginScreen } from "../screens/LoginScreen";
import { DashboardScreen } from "../screens/DashboardScreen";
import { AuthProvider } from "../auth/AuthContext";
import * as AuthContextModule from "../auth/AuthContext";
import { loginApi } from "../api/authApi";
import { tokenStorage } from "../storage/tokenStorage";

jest.mock("@react-navigation/native", () => {
  const actualNav = jest.requireActual("@react-navigation/native");
  return {
    ...actualNav,
    useNavigation: () => ({
      navigate: jest.fn(),
      dispatch: jest.fn(),
      addListener: jest.fn(() => jest.fn()),
    }),
  };
});

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn(),
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

jest.mock("../api/useDashboard", () => ({
  useDashboard: jest.fn().mockReturnValue({
    data: {
      hospital: {
        id: 99,
        name: "St. Jude Memorial",
        license_number: "LIC-998877",
        city: "Dhaka",
        status: "active",
      },
      kpis: {
        total_patients: 10,
        total_requisitions: 5,
        pending_requisitions: 2,
        approved_requisitions: 2,
        dispensed_requisitions: 1,
      },
      recent_requisitions: [],
    },
    isLoading: false,
    isError: false,
    error: null,
    refetch: jest.fn(),
    isRefetching: false,
  }),
}));

const mockUser = {
  id: 42,
  name: "Dr. Sarah Connor",
  email: "sarah@cyberdyne-health.org",
  role: "hospital_staff",
  hospital: {
    id: 99,
    name: "St. Jude Memorial",
    license_number: "LIC-998877",
    status: "active",
  },
};

describe("Login & Dashboard Screen Component Tests", () => {
  jest.setTimeout(15000);

  beforeEach(() => {
    jest.restoreAllMocks();
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);
    (tokenStorage.clearToken as jest.Mock).mockResolvedValue(true);
    (tokenStorage.setToken as jest.Mock).mockResolvedValue(true);
  });

  describe("LoginScreen UI & Form Behavior", () => {
    test("1. Password field is hidden by default and toggle button works", async () => {
      (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);

      const screen = await render(
        <AuthProvider>
          <LoginScreen />
        </AuthProvider>
      );

      await waitFor(() => {
        expect(screen.getByTestId("password-input")).toBeTruthy();
      });

      const passwordInput = screen.getByTestId("password-input");
      expect(passwordInput.props.secureTextEntry).toBe(true);

      const toggleButton = screen.getByTestId("toggle-password-visibility");
      await act(async () => {
        fireEvent.press(toggleButton);
      });

      await waitFor(() => {
        expect(screen.getByTestId("password-input").props.secureTextEntry).toBe(false);
      });
    });

    test("2. Empty fields block submission locally", async () => {
      (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);

      const screen = await render(
        <AuthProvider>
          <LoginScreen />
        </AuthProvider>
      );

      await waitFor(() => {
        expect(screen.getByTestId("login-submit-button")).toBeTruthy();
      });

      const submitButton = screen.getByTestId("login-submit-button");
      expect(submitButton.props.accessibilityState.disabled).toBe(true);
      expect(loginApi).not.toHaveBeenCalled();
    });

    test("3. Successful login triggers loginApi with email, password, and device name", async () => {
      (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);
      (loginApi as jest.Mock).mockResolvedValue({
        message: "Logged in",
        token: "token_abc123",
        user: mockUser,
      });

      const screen = await render(
        <AuthProvider>
          <LoginScreen />
        </AuthProvider>
      );

      await waitFor(() => {
        expect(screen.getByTestId("email-input")).toBeTruthy();
      });

      await act(async () => {
        fireEvent.changeText(screen.getByTestId("email-input"), "sarah@cyberdyne-health.org");
        fireEvent.changeText(screen.getByTestId("password-input"), "SafePass123!");
      });

      await act(async () => {
        fireEvent.press(screen.getByTestId("login-submit-button"));
      });

      await waitFor(() => {
        expect(loginApi).toHaveBeenCalledWith(
          "sarah@cyberdyne-health.org",
          "SafePass123!",
          expect.any(String)
        );
      });
    });

    test("4. Login error maps to safe error banner and does not leak password", async () => {
      (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);
      (loginApi as jest.Mock).mockRejectedValue({
        isAxiosError: true,
        response: { status: 401, data: { message: "Unauthenticated" } },
      });

      const screen = await render(
        <AuthProvider>
          <LoginScreen />
        </AuthProvider>
      );

      await waitFor(() => {
        expect(screen.getByTestId("email-input")).toBeTruthy();
      });

      await act(async () => {
        fireEvent.changeText(screen.getByTestId("email-input"), "sarah@cyberdyne-health.org");
        fireEvent.changeText(screen.getByTestId("password-input"), "SecretPassword123!");
      });

      await act(async () => {
        fireEvent.press(screen.getByTestId("login-submit-button"));
      });

      await waitFor(() => {
        expect(screen.getByTestId("login-error-banner")).toBeTruthy();
        expect(screen.getByText("Invalid email or password.")).toBeTruthy();
      });
    });
  });

  describe("DashboardScreen UI & Logout Behavior", () => {
    test("5. Renders all approved user and hospital profile fields", async () => {
      jest.spyOn(AuthContextModule, "useAuth").mockReturnValue({
        authState: "authenticated",
        token: "active_token_123",
        user: mockUser,
        isLoading: false,
        isAuthenticated: true,
        login: jest.fn(),
        logout: jest.fn(),
        setAuthenticated: jest.fn(),
        setUnauthenticated: jest.fn(),
      });

      const screen = await render(<DashboardScreen />);

      expect(screen.getByTestId("user-name").props.children).toBe("Dr. Sarah Connor");
      expect(screen.getByTestId("user-email").props.children).toBe("sarah@cyberdyne-health.org");
      expect(screen.getByTestId("hospital-name").props.children).toBe("St. Jude Memorial");
      expect(screen.getByTestId("hospital-license").props.children).toBe("LIC-998877");
    });

    test("6. Logout network failure displays error banner and remains on Dashboard", async () => {
      const mockLogout = jest.fn().mockRejectedValue({
        isAxiosError: true,
        response: undefined, // Network failure
      });

      jest.spyOn(AuthContextModule, "useAuth").mockReturnValue({
        authState: "authenticated",
        token: "active_token_123",
        user: mockUser,
        isLoading: false,
        isAuthenticated: true,
        login: jest.fn(),
        logout: mockLogout,
        setAuthenticated: jest.fn(),
        setUnauthenticated: jest.fn(),
      });

      const screen = await render(<DashboardScreen />);

      expect(screen.getByTestId("user-name").props.children).toBe("Dr. Sarah Connor");

      await act(async () => {
        fireEvent.press(screen.getByTestId("logout-button"));
      });

      await waitFor(() => {
        expect(screen.getByTestId("dashboard-error-banner")).toBeTruthy();
        expect(
          screen.getByText("Unable to connect to the hospital server. Check your connection and try again.")
        ).toBeTruthy();
      });

      expect(mockLogout).toHaveBeenCalled();
    });
  });
});
