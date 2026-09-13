import React from "react";
import { AuthProvider, useAuth, SessionValidator } from "../auth/AuthContext";
import { tokenStorage } from "../storage/tokenStorage";
import { resetSessionExpiryCoordinatorForTests, triggerSessionExpiry } from "../auth/sessionExpiryCoordinator";
import { loginApi, getMeApi, logoutApi } from "../api/authApi";
import { render, waitFor, act, fireEvent } from "@testing-library/react-native";
import { Text, Button } from "react-native";

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

const mockUser = {
  id: 1,
  name: "Dr. John Smith",
  email: "john@hospital.org",
  role: "hospital_staff",
  hospital: {
    id: 5,
    name: "General Hospital",
    license_number: "HOSP-001",
    status: "active",
  },
};

const TestConsumer: React.FC = () => {
  const { authState, token, user, login, logout, setAuthenticated, setUnauthenticated } = useAuth();
  return (
    <>
      <Text testID="auth-state">{`${authState}:${token || "null"}`}</Text>
      <Text testID="user-name">{user?.name || "null"}</Text>
      <Button title="Login" onPress={() => { login("john@hospital.org", "Password123!").catch(() => {}); }} />
      <Button title="Logout" onPress={() => { logout().catch(() => {}); }} />
      <Button title="LegacyLogin" onPress={() => { setAuthenticated("legacy_token").catch(() => {}); }} />
      <Button title="LegacyLogout" onPress={() => { setUnauthenticated().catch(() => {}); }} />
    </>
  );
};

describe("AuthContext & AuthProvider Unit Tests", () => {
  jest.setTimeout(15000);

  beforeEach(() => {
    jest.resetAllMocks();
    resetSessionExpiryCoordinatorForTests();
    (tokenStorage.clearToken as jest.Mock).mockResolvedValue(true);
    (tokenStorage.setToken as jest.Mock).mockResolvedValue(true);
  });

  test("1. No token bootstraps directly to unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);

    const screen = await render(
      <AuthProvider validationTimeoutMs={100}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
      expect(screen.getByTestId("user-name").props.children).toBe("null");
    });
  });

  test("2. Startup with valid token calls getMeApi and reaches authenticated state with user profile", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_saved_token");
    (getMeApi as jest.Mock).mockResolvedValue({ user: mockUser });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:valid_saved_token");
      expect(screen.getByTestId("user-name").props.children).toBe("Dr. John Smith");
    });
    expect(getMeApi).toHaveBeenCalled();
  });

  test("3. Startup 401 from getMeApi clears token storage and sets unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("expired_token");
    (getMeApi as jest.Mock).mockRejectedValue({
      isAxiosError: true,
      response: { status: 401, data: { message: "Unauthenticated" } },
    });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("4. Startup network/5xx failure does NOT delete stored token from SecureStore", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_saved_token");
    (getMeApi as jest.Mock).mockRejectedValue({
      isAxiosError: true,
      response: undefined, // Network error
    });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).not.toHaveBeenCalled();
  });

  test("5. Real login calls loginApi, stores token in SecureStore, and sets user profile", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);
    (loginApi as jest.Mock).mockResolvedValue({
      message: "Login successful",
      token: "new_api_token_999",
      user: mockUser,
    });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });

    await act(async () => {
      fireEvent.press(screen.getByText("Login"));
    });

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:new_api_token_999");
      expect(screen.getByTestId("user-name").props.children).toBe("Dr. John Smith");
    });
    expect(tokenStorage.setToken).toHaveBeenCalledWith("new_api_token_999");
  });

  test("6. Real login storage failure calls best-effort logoutApi and throws error", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);
    (loginApi as jest.Mock).mockResolvedValue({
      message: "Login successful",
      token: "new_api_token_999",
      user: mockUser,
    });
    (tokenStorage.setToken as jest.Mock).mockResolvedValue(false);
    (logoutApi as jest.Mock).mockResolvedValue({ message: "revoked" });

    let capturedLogin: ((e: string, p: string) => Promise<void>) | null = null;
    const Grabber: React.FC = () => {
      const auth = useAuth();
      React.useEffect(() => {
        capturedLogin = auth.login;
      }, [auth]);
      return <Text testID="auth-state">{auth.authState}</Text>;
    };

    await render(
      <AuthProvider>
        <Grabber />
      </AuthProvider>
    );

    await act(async () => {
      if (capturedLogin) {
        await expect(capturedLogin("john@hospital.org", "Password123!")).rejects.toThrow(
          "SecureStore persistence failed"
        );
      }
    });

    expect(logoutApi).toHaveBeenCalled();
  });

  test("7. Real logout calls logoutApi, clears SecureStore, and resets user profile", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("active_token");
    (getMeApi as jest.Mock).mockResolvedValue({ user: mockUser });
    (logoutApi as jest.Mock).mockResolvedValue({ message: "Logged out" });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:active_token");
    });

    await act(async () => {
      fireEvent.press(screen.getByText("Logout"));
    });

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
      expect(screen.getByTestId("user-name").props.children).toBe("null");
    });
    expect(logoutApi).toHaveBeenCalled();
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("8. Real logout 401 treats token as already revoked and clears local session", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("revoked_token");
    (getMeApi as jest.Mock).mockResolvedValue({ user: mockUser });
    (logoutApi as jest.Mock).mockRejectedValue({
      isAxiosError: true,
      response: { status: 401, data: { message: "Unauthenticated" } },
    });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:revoked_token");
    });

    await act(async () => {
      fireEvent.press(screen.getByText("Logout"));
    });

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("9. Real logout network error preserves session and token in state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_active_token");
    (getMeApi as jest.Mock).mockResolvedValue({ user: mockUser });
    (logoutApi as jest.Mock).mockRejectedValue({
      isAxiosError: true,
      response: undefined, // Network failure
    });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:valid_active_token");
    });

    await act(async () => {
      fireEvent.press(screen.getByText("Logout"));
    });

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:valid_active_token");
    });
  });

  test("10. Centralized 401 session expiry clears user and session state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("active_token");
    (getMeApi as jest.Mock).mockResolvedValue({ user: mockUser });

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:active_token");
    });

    await act(async () => {
      await triggerSessionExpiry();
    });

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
      expect(screen.getByTestId("user-name").props.children).toBe("null");
    });
  });

  test("11. Legacy sessionValidator prop support for custom tests", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("custom_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(true),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:custom_token");
    });
    expect(mockValidator.validateToken).toHaveBeenCalledWith("custom_token");
  });
});
