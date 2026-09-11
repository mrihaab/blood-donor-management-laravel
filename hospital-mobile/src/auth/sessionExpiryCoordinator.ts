import { tokenStorage } from "../storage/tokenStorage";
import { queryClient } from "../api/queryClient";

type ExpiryHandler = () => void | Promise<void>;

let handlers: Set<ExpiryHandler> = new Set();
let inFlightExpiryPromise: Promise<void> | null = null;
let activeSessionId: string | null = null;

export function notifySessionStarted(sessionId?: string): string {
  activeSessionId = sessionId || `sess_${Date.now()}_${Math.random()}`;
  inFlightExpiryPromise = null;
  return activeSessionId;
}

export function getActiveSessionId(): string | null {
  return activeSessionId;
}

export function registerSessionExpiryHandler(handler: ExpiryHandler): () => void {
  handlers.add(handler);
  return () => {
    handlers.delete(handler);
  };
}

export function triggerSessionExpiry(sessionId?: string): Promise<void> {
  // If a session ID is passed and it doesn't match the current active session, ignore late 401
  if (sessionId && activeSessionId && sessionId !== activeSessionId) {
    return Promise.resolve();
  }

  if (inFlightExpiryPromise) {
    return inFlightExpiryPromise;
  }

  inFlightExpiryPromise = (async () => {
    try {
      await tokenStorage.clearToken().catch(() => {});
    } finally {
      try {
        queryClient.clear();
      } catch {}

      const registeredHandlers = Array.from(handlers);
      for (const handler of registeredHandlers) {
        try {
          await handler();
        } catch {}
      }
    }
  })();

  return inFlightExpiryPromise;
}

export function resetSessionExpiryCoordinatorForTests(): void {
  handlers.clear();
  inFlightExpiryPromise = null;
  activeSessionId = null;
}