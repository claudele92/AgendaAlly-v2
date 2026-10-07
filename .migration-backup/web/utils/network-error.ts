export default class NetworkError extends Error {
  constructor(
    public message: string,
    public statusCode: number,
    public params?: Record<string, string[]>,
    // The backend's own machine-readable error code (ErrorResponse.statusCode,
    // e.g. "LOCATION_AMBIGUOUS") - distinct from `statusCode` above, which is
    // the raw HTTP status. Lets a caller branch on a specific error without
    // matching on the translated, locale-dependent `message` string.
    public code?: string
  ) {
    super(message);

    Object.setPrototypeOf(this, NetworkError.prototype);
  }
}
