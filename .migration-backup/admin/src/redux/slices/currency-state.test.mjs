import test from 'node:test';
import assert from 'node:assert/strict';
import currencyReducer, {
  initialCurrencyState,
  setCurrencySession,
} from './currency-state.mjs';

const originalUser = {
  id: 61,
  role: 'admin',
  token: 'opaque-session-one',
};
const nextCurrencyUser = {
  id: 62,
  role: 'manager',
  token: 'opaque-session-two',
};
const currencyRows = [
  { id: 1, title: 'USD', default: true },
  { id: 2, title: 'EUR', default: false },
];

function startRestCurrencyRequest(state, requestId = 'request-1') {
  return currencyReducer(state, {
    type: 'currency/fetchRestCurrencies/pending',
    meta: { requestId },
  });
}

function fulfillRestCurrencyRequest(state, requestId = 'request-1') {
  return currencyReducer(state, {
    type: 'currency/fetchRestCurrencies/fulfilled',
    meta: { requestId },
    payload: { data: currencyRows },
  });
}

function loadedCurrencyState() {
  return fulfillRestCurrencyRequest(
    startRestCurrencyRequest(
      currencyReducer(initialCurrencyState, setCurrencySession(originalUser)),
    ),
  );
}

test('same-session auth setters preserve loaded currency state and an in-flight request', () => {
  const loaded = loadedCurrencyState();
  assert.strictEqual(
    currencyReducer(loaded, {
      type: 'auth/setUserData',
      payload: { ...originalUser, name: 'Updated profile' },
    }),
    loaded,
  );

  const pending = startRestCurrencyRequest(
    currencyReducer(loaded, { type: 'currency/resetCurrency' }),
    'same-session-pending',
  );
  const sameSessionPending = currencyReducer(pending, {
    type: 'auth/setUserData',
    payload: { ...originalUser, name: 'Updated profile' },
  });
  assert.strictEqual(sameSessionPending, pending);
  assert.equal(sameSessionPending.activeRequestId, 'same-session-pending');
  assert.equal(sameSessionPending.loading, true);

  const completed = fulfillRestCurrencyRequest(
    sameSessionPending,
    'same-session-pending',
  );
  assert.deepEqual(completed.currencies, currencyRows);
  assert.deepEqual(completed.defaultCurrency, currencyRows[0]);
  assert.equal(completed.activeRequestId, null);
});

test('the startup session action preserves matching actors and immediately clears changed identities', () => {
  const loaded = loadedCurrencyState();
  assert.strictEqual(
    currencyReducer(loaded, setCurrencySession({ ...originalUser })),
    loaded,
  );

  const changed = currencyReducer(loaded, setCurrencySession(nextCurrencyUser));
  assert.deepEqual(changed.currencies, []);
  assert.deepEqual(changed.defaultCurrency, {});
  assert.equal(changed.activeRequestId, null);
  assert.deepEqual(changed.sessionIdentity, {
    userId: '62',
    role: 'manager',
    token: 'opaque-session-two',
  });
});

test('account, role, and token changes clear populated and pending state; late responses are ignored', () => {
  const identityChanges = [
    { ...originalUser, id: 62 },
    { ...originalUser, role: 'manager' },
    { ...originalUser, token: 'opaque-session-two' },
  ];

  for (const changedUser of identityChanges) {
    const pending = startRestCurrencyRequest(
      currencyReducer(loadedCurrencyState(), {
        type: 'currency/resetCurrency',
      }),
      'old-session-request',
    );
    const changed = currencyReducer(pending, {
      type: 'auth/setUserData',
      payload: changedUser,
    });

    assert.deepEqual(changed.currencies, []);
    assert.deepEqual(changed.defaultCurrency, {});
    assert.equal(changed.loading, false);
    assert.equal(changed.activeRequestId, null);

    const lateResponse = fulfillRestCurrencyRequest(
      changed,
      'old-session-request',
    );
    assert.strictEqual(lateResponse, changed);
    assert.deepEqual(lateResponse.currencies, []);
  }
});

test('a late protected admin-currency response cannot repopulate a changed session', () => {
  let state = currencyReducer(initialCurrencyState, setCurrencySession(originalUser));
  state = currencyReducer(state, {
    type: 'currency/fetchCurrencies/pending',
    meta: { requestId: 'admin-currency-old-session' },
  });
  state = currencyReducer(state, {
    type: 'auth/setUserData',
    payload: { ...originalUser, token: 'opaque-session-two' },
  });

  const afterLateResponse = currencyReducer(state, {
    type: 'currency/fetchCurrencies/fulfilled',
    meta: { requestId: 'admin-currency-old-session' },
    payload: { data: currencyRows },
  });
  assert.strictEqual(afterLateResponse, state);
  assert.deepEqual(afterLateResponse.currencies, []);
});

test('logout immediately clears session identity and rejects the previous response', () => {
  const pending = startRestCurrencyRequest(
    currencyReducer(loadedCurrencyState(), {
      type: 'currency/resetCurrency',
    }),
    'logout-pending',
  );
  const loggedOut = currencyReducer(pending, { type: 'auth/clearUser' });

  assert.deepEqual(loggedOut.currencies, []);
  assert.deepEqual(loggedOut.defaultCurrency, {});
  assert.equal(loggedOut.loading, false);
  assert.equal(loggedOut.activeRequestId, null);
  assert.equal(loggedOut.sessionIdentity, null);
  assert.strictEqual(
    fulfillRestCurrencyRequest(loggedOut, 'logout-pending'),
    loggedOut,
  );
});