import React from "react";
import { AuthProvider, useAuth, SessionValidator } from "../auth/AuthContext";
import { tokenStorage } from "../storage/tokenStorage";
import { render, waitFor } from "@testing-library/react-native";
import { Text } from "react-native";

jest.mock("../storage/tokenStorage", () => ({
  tokenStorage: {
    getToken: jest.fn(),
    setToken: jest.fn(),
    clearToken: jest.fn().mockResolvedValue(true),
  },
}));

const TestConsumer: React.FC = () => {
  const { authState, token } = useAuth();
  return <Text testID="auth-state">{`${authState}:${token || "null"}`}</Text>;
};

describe("AuthContext & AuthProvider Unit Tests", () => {
  beforeEach(() => {
    jest.clearAllMocks();
  });

  test("1. No token bootstraps directly to unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue(null);

    const screen = await render(
      <AuthProvider>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").children.join("")).toBe("unauthenticated:null");
    });
  });

  test("2. Valid mocked token validation reaches authenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("valid_saved_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(true),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").children.join("")).toBe("authenticated:valid_saved_token");
    });
    expect(mockValidator.validateToken).toHaveBeenCalledWith("valid_saved_token");
  });

  test("3. Invalid token clears storage and reaches unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("invalid_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockResolvedValue(false),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").children.join("")).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("4. Validator rejection clears storage and reaches unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("rejected_token");
    const mockValidator: SessionValidator = {
      validateToken: jest.fn().mockRejectedValue(new Error("Network validation error")),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(() => {
      expect(screen.getByTestId("auth-state").children.join("")).toBe("unauthenticated:null");
    });
    expect(tokenStorage.clearToken).toHaveBeenCalled();
  });

  test("5. Validator timeout reaches unauthenticated state", async () => {
    (tokenStorage.getToken as jest.Mock).mockResolvedValue("hanging_token");
    const mockValidator: SessionValidator = {
      validateToken: () => new Promise(() => {}),
    };

    const screen = await render(
      <AuthProvider sessionValidator={mockValidator}>
        <TestConsumer />
      </AuthProvider>
    );

    await waitFor(
      () => {
        expect(screen.getByTestId("auth-state").children.join("")).toBe("unauthenticated:null");
      },
      { timeout: 6000 }
    );
  });
});
