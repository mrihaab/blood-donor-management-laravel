import React from "react";
import { AuthProvider, useAuth, SessionValidator } from "../auth/AuthContext";
import { tokenStorage } from "../storage/tokenStorage";
import { resetSessionExpiryCoordinatorForTests } from "../auth/sessionExpiryCoordinator";
import { render, waitFor, act, fireEvent } from "@testing-library/react-native";
import { Text, Button } from "react-native";

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn(),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

const TestConsumer: React.FC = () => {
  const { authState, token, setAuthenticated, setUnauthenticated } = useAuth();
  return (
    <>
      <Text testID="auth-state">{`${authState}:${token || "null"}`}</Text>
      <Button title="Login" onPress={() => setAuthenticated("new_valid_token")} />
      <Button title="Logout" onPress={() => setUnauthenticated()} />
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

    const screen = render(
      <AuthProvider validationTimeoutMs={100}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
  });

  test("2. Valid mocked token validation reaches authenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_saved_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(true),
    };

    const screen = render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:valid_saved_token");
    });
    expect(mockValidator.validateToken).toHaveBeenCalledWith("valid_saved_token");
  });

  test("3. Invalid token clears storage and reaches unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("invalid_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(false),
    };

    const screen = render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("4. Validator rejection clears storage and reaches unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("rejected_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockRejectedValue(new Error("Network validation error")),
    };

    const screen = render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("5. Validator timeout reaches unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("hanging_token");
    const mockValidator: SessionValidator = {
      validateToken: () => new Promise(() => {}),
    };

    const screen = render(
      <AuthProvider sessionValidator={mockValidator} validationTimeoutMs={100}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(
      () => {
        expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
      },
      { timeout: 2000 }
    );
  });

  test("6. Storage write failure during login throws error and keeps unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);
    (tokenStorage.setToken as jest.Mock).mockResolvedValue(false);

    let capturedState = "unauthenticated";
    let capturedSetAuth: ((t: string) => Promise<void>) | null = null;

    const Grabber: React.FC = () => {
      const auth = useAuth();
      React.useEffect(() => {
        capturedState = auth.authState;
        capturedSetAuth = auth.setAuthenticated;
      }, [auth]);
      return <Text testID="auth-state">{auth.authState}</Text>;
    };

    render(
      <AuthProvider>
        <Grabber />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(capturedState).toBe("unauthenticated");
    });

    await act(async () => {
      if (capturedSetAuth) {
        await expect(capturedSetAuth("failed_token")).rejects.toThrow(
          "SecureStore persistence failed"
        );
      }
    });

    expect(capturedState).toBe("unauthenticated");
  });

  test("7. Storage deletion failure during logout clears memory state to unauthenticated", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_saved_token");
    (tokenStorage.clearToken as jest.Mock).mockRejectedValue(new Error("Storage delete error"));

    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(true),
    };

    const screen = render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("authenticated:valid_saved_token");
    });

    await act(async () => {
      fireEvent.press(screen.getByText("Logout"));
    });

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").props.children).toBe("unauthenticated:null");
    });
  });

  test("8. Unmount during token validation prevents post-unmount state updates", async () => {
    let resolveValidation: (res: boolean) => void = () => {};
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("slow_token");
    const mockValidator: SessionValidator = {
      validateToken: () =>
        new Promise((resolve) => {
          resolveValidation = resolve;
        }),
    };

    const screen = render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    // Unmount before validation resolves
    screen.unmount();

    // Resolve after unmount
    await act(async () => {
      resolveValidation(true);
    });
    // No error thrown on post-unmount state update
  });
});
