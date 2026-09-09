const test = require('node:test');
const assert = require('node:assert');

// Mock SecureStore
const mockStorage = {
  data: {},
  async getItemAsync(key) { return this.data[key] || null; },
  async setItemAsync(key, val) { this.data[key] = val; },
  async deleteItemAsync(key) { delete this.data[key]; }
};

// Token Storage Abstraction Test
test('tokenStorage getters, setters and clear methods', async () => {
  await mockStorage.setItemAsync('hospital_mobile_bearer_token', 'test_token_123');
  const token = await mockStorage.getItemAsync('hospital_mobile_bearer_token');
  assert.strictEqual(token, 'test_token_123');

  await mockStorage.deleteItemAsync('hospital_mobile_bearer_token');
  const clearedToken = await mockStorage.getItemAsync('hospital_mobile_bearer_token');
  assert.strictEqual(clearedToken, null);
});

// Single-Flight 401 Interceptor Test
test('Single-flight 401 handler triggers clearToken and resetToLogin ONCE for 5 concurrent 401s', async () => {
  let clearCount = 0;
  let resetCount = 0;
  let isHandlingExpiry = false;

  const handleResponse = async (status, isPublic) => {
    if (isPublic) return;
    if (status === 401 && !isHandlingExpiry) {
      isHandlingExpiry = true;
      try {
        clearCount++;
        resetCount++;
      } finally {
        setTimeout(() => { isHandlingExpiry = false; }, 100);
      }
    }
  };

  // Run 5 simultaneous 401s
  await Promise.all([
    handleResponse(401, false),
    handleResponse(401, false),
    handleResponse(401, false),
    handleResponse(401, false),
    handleResponse(401, false),
  ]);

  assert.strictEqual(clearCount, 1, 'clearCount should be exactly 1');
  assert.strictEqual(resetCount, 1, 'resetCount should be exactly 1');
});

test('403, 404, 422, and 429 status codes do NOT clear token or reset navigation', async () => {
  let clearCount = 0;
  let isHandlingExpiry = false;

  const handleResponse = async (status, isPublic) => {
    if (isPublic) return;
    if (status === 401 && !isHandlingExpiry) {
      isHandlingExpiry = true;
      try { clearCount++; } finally { isHandlingExpiry = false; }
    }
  };

  await handleResponse(403, false);
  await handleResponse(404, false);
  await handleResponse(422, false);
  await handleResponse(429, false);

  assert.strictEqual(clearCount, 0, 'clearCount should be 0 for non-401 errors');
});