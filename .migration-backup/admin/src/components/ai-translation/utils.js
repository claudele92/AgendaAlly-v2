// === AI Translation State management ===

// --- Action Types ---
export const AI_TRANSLATION_ACTION_TYPES = {
  SET_LOADING: 'SET_LOADING',
  SET_RESULT: 'SET_RESULT',
};

// --- Action Creators ---

export const AI_TRANSLATION_ACTIONS = {
  /**
   * @param {boolean} payload
   * @returns {{ type: 'SET_LOADING', payload: boolean }}
   */
  setLoading: (payload) => ({
    type: AI_TRANSLATION_ACTION_TYPES.SET_LOADING,
    payload,
  }),
  /**
   * @param {'success' | 'error' | null} payload
   * @returns {{ type: 'SET_RESULT', payload: 'success' | 'error' | null }}
   */
  setResult: (payload) => ({
    type: AI_TRANSLATION_ACTION_TYPES.SET_RESULT,
    payload,
  }),
};

// --- Initial State ---

export const initialState = {
  isLoading: false,
  result: null,
};

// --- Reducer ---

export const reducer = (state, action) => {
  switch (action.type) {
    case AI_TRANSLATION_ACTION_TYPES.SET_LOADING:
      return { ...state, isLoading: action.payload };
    case AI_TRANSLATION_ACTION_TYPES.SET_RESULT:
      return { ...state, result: action.payload };
    default:
      return state;
  }
};
