export function isKnownMissingCartError(error) {
  if (!(error instanceof Error) || error.statusCode !== 404) return false;

  // Native responses preserve the machine code, independent of translation.
  // A conflicting code must not be hidden by a matching display message.
  if (error.code != null) return error.code === "ERROR_404";

  return error.message.trim().toLowerCase() === "items not found";
}