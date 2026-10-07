import React, {
  createContext,
  useCallback,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
} from 'react';

const NavigationScopeContext = createContext({
  status: 'unavailable',
  scope: null,
  error: null,
  sessionMatches: false,
  refresh: async () => null,
});

/**
 * Holds only fresh, authenticated navigation scope in memory. The injected
 * loader uses the additive self-context endpoint; neither role strings nor
 * user.urls can serve as a substitute for its effective server grants.
 */
export function NavigationScopeProvider({
  userId,
  role,
  sessionKey,
  fetchScope,
  children,
}) {
  const scopeSessionKey = JSON.stringify([
    userId ?? null,
    role ?? null,
    sessionKey ?? null,
  ]);
  const [state, setState] = useState({
    status: 'unavailable',
    scope: null,
    error: null,
    sessionKey: scopeSessionKey,
  });
  const generation = useRef(0);

  const refresh = useCallback(async () => {
    if (userId === null || userId === undefined) {
      generation.current += 1;
      setState({
        status: 'idle',
        scope: null,
        error: null,
        sessionKey: scopeSessionKey,
      });
      return null;
    }
    if (typeof fetchScope !== 'function') {
      setState({
        status: 'unavailable',
        scope: null,
        error: null,
        sessionKey: scopeSessionKey,
      });
      return null;
    }

    const requestGeneration = ++generation.current;
    setState({
      status: 'loading',
      scope: null,
      error: null,
      sessionKey: scopeSessionKey,
    });
    try {
      // Do not normalize the response envelope until the owning endpoint
      // contract is supplied; callers must inject its explicit adapter.
      const scope = await fetchScope({ userId });
      if (generation.current !== requestGeneration) return null;
      setState({ status: 'ready', scope, error: null, sessionKey: scopeSessionKey });
      return scope;
    } catch (error) {
      if (generation.current === requestGeneration) {
        setState({
          status: 'error',
          scope: null,
          error,
          sessionKey: scopeSessionKey,
        });
      }
      return null;
    }
  }, [fetchScope, role, scopeSessionKey, userId]);

  useEffect(() => {
    generation.current += 1;
    setState({
      status: userId === null || userId === undefined ? 'idle' : 'unavailable',
      scope: null,
      error: null,
      sessionKey: scopeSessionKey,
    });
    if (userId !== null && userId !== undefined && fetchScope) refresh();

    return () => {
      generation.current += 1;
    };
  }, [fetchScope, refresh, role, scopeSessionKey, sessionKey, userId]);

  useEffect(() => {
    if (userId === null || userId === undefined || !fetchScope) return undefined;
    const onFocus = () => refresh();
    window.addEventListener('focus', onFocus);
    return () => window.removeEventListener('focus', onFocus);
  }, [fetchScope, refresh, userId]);

  const sessionMatches = state.sessionKey === scopeSessionKey;
  const value = useMemo(
    () => ({
      status: sessionMatches ? state.status : 'unavailable',
      scope: sessionMatches ? state.scope : null,
      error: sessionMatches ? state.error : null,
      sessionMatches,
      refresh,
    }),
    [refresh, sessionMatches, state],
  );
  return (
    <NavigationScopeContext.Provider value={value}>
      {children}
    </NavigationScopeContext.Provider>
  );
}

export function useNavigationScope() {
  return useContext(NavigationScopeContext);
}