import { tokenStorage } from "../storage/tokenStorage";
import { queryClient } from "../api/queryClient";

type ExpiryHandler = () => void | Promise<void>;

let handlers: Set<ExpiryHandler> = new Set();
let inFlightExpiryPromise: Promise<void> | null = null;
let activeSessionId: string | null = null;

export function notifySessionStarted(sessionId?: string): void {
  activeSessionId = sessionId || `sess_${Date.now()}_${Math.random()}`;
  inFlightExpiryPromise = null;
}

export function registerSessionExpiryHandler(handler: ExpiryHandler): () => void {
  handlers.add(handler);
  return () => {
    handlers.delete(handler);
  };
}

export function triggerSessionExpiry(): Promise<void> {
  if (inFlightExpiryPromise) {
    return inFlightExpiryPromise;
  }

  inFlightExpiryPromise = (async () => {
    try {
      await tokenStorage.clearToken().catch(() => {});
    } finally {
      try {
        queryClient.clear();
      } catch (e) {}

      const registeredHandlers = Array.from(handlers);
      for (const handler of registeredHandlers) {
        try {
          await handler();
        } catch (e) {}
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