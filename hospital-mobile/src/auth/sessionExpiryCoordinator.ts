import { tokenStorage } from "../storage/tokenStorage";
import { queryClient } from "../api/queryClient";

let sessionExpiryHandler: (() => Promise<void>) | null = null;
let isHandlingExpiry = false;

export function registerSessionExpiryHandler(handler: () => Promise<void>) {
  sessionExpiryHandler = handler;
}

export async function triggerSessionExpiry(): Promise<void> {
  if (isHandlingExpiry) {
    return;
  }
  isHandlingExpiry = true;
  try {
    await tokenStorage.clearToken();
    queryClient.clear();
    if (sessionExpiryHandler) {
      await sessionExpiryHandler();
    }
  } finally {
    isHandlingExpiry = false;
  }
}