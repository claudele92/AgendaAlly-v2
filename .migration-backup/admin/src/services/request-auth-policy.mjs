/** An explicitly object-scoped denial is not evidence of an invalid session. */
export function shouldClearAuthForResponse(status, config = {}) {
  return status === 401 ||
    (status === 403 && config.preserveAuthOnForbidden !== true);
}
