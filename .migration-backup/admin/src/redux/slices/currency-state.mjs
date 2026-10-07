export const initialCurrencyState = {
  loading: false,
  currencies: [],
  defaultCurrency: {},
  error: '',
  activeRequestId: null,
  sessionIdentity: null,
};

export function resetCurrency() {
  return { type: 'currency/resetCurrency' };
}

export function setCurrencySession(user) {
  return { type: 'currency/setSession', payload: user };
}

function identityFor(user) {
  if (!user || user.id === null || user.id === undefined) return null;
  return {
    userId: String(user.id),
    role: user.role ?? null,
    token: user.token ?? null,
  };
}

function sameIdentity(left, right) {
  return (
    left === right ||
    (left !== null &&
      right !== null &&
      left.userId === right.userId &&
      left.role === right.role &&
      left.token === right.token)
  );
}

function clearCurrency(state, sessionIdentity = null) {
  return {
    ...state,
    loading: false,
    currencies: [],
    defaultCurrency: {},
    error: '',
    activeRequestId: null,
    sessionIdentity,
  };
}

function setSessionIdentity(state, user) {
  const nextIdentity = identityFor(user);
  if (sameIdentity(state.sessionIdentity, nextIdentity)) return state;
  return clearCurrency(state, nextIdentity);
}

function requestPending(state, action) {
  return {
    ...state,
    loading: true,
    activeRequestId: action.meta.requestId,
  };
}

function requestFulfilled(state, action) {
  if (state.activeRequestId !== action.meta.requestId) return state;
  const currencies = action.payload.data;
  return {
    ...state,
    loading: false,
    currencies,
    defaultCurrency: currencies.find((item) => item.default),
    error: '',
    activeRequestId: null,
  };
}

function requestRejected(state, action) {
  if (state.activeRequestId !== action.meta.requestId) return state;
  return {
    ...state,
    loading: false,
    currencies: [],
    error: action.error.message,
    activeRequestId: null,
  };
}

export default function currencyReducer(state = initialCurrencyState, action) {
  switch (action.type) {
    case 'currency/resetCurrency':
      return clearCurrency(state, state.sessionIdentity);
    case 'currency/setSession':
    case 'auth/setUserData':
      return setSessionIdentity(state, action.payload);
    case 'auth/updateUser': {
      const currentIdentity = state.sessionIdentity;
      const user = {
        id: currentIdentity?.userId,
        role: currentIdentity?.role,
        token: currentIdentity?.token,
        ...action.payload,
      };
      return setSessionIdentity(state, user);
    }
    case 'auth/clearUser':
      return clearCurrency(state);
    case 'currency/fetchCurrencies/pending':
    case 'currency/fetchRestCurrencies/pending':
      return requestPending(state, action);
    case 'currency/fetchCurrencies/fulfilled':
    case 'currency/fetchRestCurrencies/fulfilled':
      return requestFulfilled(state, action);
    case 'currency/fetchCurrencies/rejected':
    case 'currency/fetchRestCurrencies/rejected':
      return requestRejected(state, action);
    default:
      return state;
  }
}